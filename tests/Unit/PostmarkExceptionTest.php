<?php

use Illuminate\Http\Client\Response;
use JeffersonGoncalves\Postmark\Exceptions\PostmarkException;

function fakePostmarkResponse(int $status, array $body): Response
{
    return new Response(new GuzzleHttp\Psr7\Response($status, [], json_encode($body)));
}

it('builds the exception from the Postmark error body', function () {
    $response = fakePostmarkResponse(422, ['ErrorCode' => 300, 'Message' => 'Invalid email request']);

    $exception = PostmarkException::fromResponse($response);

    expect($exception->getMessage())->toBe('Invalid email request')
        ->and($exception->getCode())->toBe(300)
        ->and($exception->errorBody())->toBe(['ErrorCode' => 300, 'Message' => 'Invalid email request']);
});

it('falls back to the HTTP status when the body has no ErrorCode', function () {
    $exception = PostmarkException::fromResponse(fakePostmarkResponse(500, []));

    expect($exception->getMessage())->toBe('Postmark API error (HTTP 500).')
        ->and($exception->getCode())->toBe(500);
});
