<?php

declare(strict_types=1);

namespace Dreamteam\Test\Controller;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter\Attribute\Rule\DisablePrefilters;
use Bitrix\Main\Engine\ActionFilter\Attribute\Rule\HttpMethod;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Validation\ValidationService;
use Dreamteam\Test\Dto\NewsDto;
use Dreamteam\Test\Request\AddNewRequest;
use Dreamteam\Test\Request\ListNewsRequest;
use Dreamteam\Test\Request\NewsRequestParser;
use Dreamteam\Test\Service\NewsService;
use InvalidArgumentException;
use Throwable;

final class NewsController extends Controller
{
    private const HTTP_CREATED = 201;
    private const HTTP_BAD_REQUEST = 400;
    private const HTTP_NOT_FOUND = 404;
    private const HTTP_INTERNAL_SERVER_ERROR = 500;

    public function getAutoWiredParameters(): array
    {
        return [
            new Parameter(
                AddNewRequest::class,
                fn (): AddNewRequest => NewsRequestParser::parseAdd($this->getRequest()->getJsonList()->toArray()),
            ),
            new Parameter(ListNewsRequest::class, function (): ListNewsRequest {
                $query = $this->getRequest()->getQueryList();

                try {
                    return NewsRequestParser::parseList(
                        $query->get('date_from'),
                        $query->get('date_to'),
                        $query->get('name'),
                    );
                } catch (InvalidArgumentException $exception) {
                    Context::getCurrent()->getResponse()->setStatus(self::HTTP_BAD_REQUEST);
                    throw new BinderArgumentException($exception->getMessage());
                }
            }),
        ];
    }

    #[HttpMethod(['POST'])]
    #[DisablePrefilters([Authentication::class, Csrf::class])]
    public function addAction(
        NewsService $newsService,
        AddNewRequest $request,
        ValidationService $validator,
    ): Json {
        $validation = $validator->validate($request);
        if (!$validation->isSuccess()) {
            return $this->errorResponse(self::HTTP_BAD_REQUEST, implode('; ', $validation->getErrorMessages()));
        }

        try {
            $id = $newsService->add($request->name, $request->code, $request->description);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception);
        }

        return (new Json(['id' => $id]))->setStatus(self::HTTP_CREATED);
    }

    #[HttpMethod(['GET'])]
    #[DisablePrefilters([Authentication::class, Csrf::class])]
    public function listAction(NewsService $newsService, ListNewsRequest $request): Json
    {
        try {
            $newsList = $newsService->getList($request->dateFrom, $request->dateToExclusive, $request->name);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception);
        }

        return new Json(array_map(static fn (NewsDto $news): array => $news->toArray(), $newsList));
    }

    #[HttpMethod(['GET'])]
    #[DisablePrefilters([Authentication::class, Csrf::class])]
    public function detailAction(NewsService $newsService, int $id): Json
    {
        try {
            $news = $newsService->findById($id);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception);
        }

        return $news === null
            ? $this->errorResponse(self::HTTP_NOT_FOUND, Loc::getMessage('DREAMTEAM_TEST_NEWS_NOT_FOUND'))
            : new Json($news->toArray());
    }

    private function errorResponse(int $status, ?string $message): Json
    {
        return (new Json(['error' => $message]))->setStatus($status);
    }

    private function serverErrorResponse(Throwable $exception): Json
    {
        $this->writeToLogException($exception);

        return $this->errorResponse(
            self::HTTP_INTERNAL_SERVER_ERROR,
            Loc::getMessage('DREAMTEAM_TEST_NEWS_INTERNAL_ERROR'),
        );
    }
}
