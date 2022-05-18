<?php

$I = new ApiTester($scenario);

$I->comment('Testing with boolean true');
$yamlFilename = 'castBooleanTrue.yaml';
$uri = $I->getMyBaseUri() . '/cast/boolean';
$I->performLogin(getenv('TESTER_DEVELOPER_NAME'), getenv('TESTER_DEVELOPER_PASS'));
$I->createResourceFromYaml($yamlFilename);

$I->wantTo('Test cast boolean true to array.');
$I->sendGet($uri, ['data_type' => 'array']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => [true],
]);

$I->wantTo('Test cast boolean true to boolean.');
$I->sendGet($uri, ['data_type' => 'boolean']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => true,
]);

$I->wantTo('Test cast boolean true to empty.');
$I->sendGet($uri, ['data_type' => 'undefined']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => null,
]);

$I->wantTo('Test cast boolean true to float.');
$I->sendGet($uri, ['data_type' => 'float']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => 1.0,
]);

$I->wantTo('Test cast boolean true to html.');
$I->sendGet($uri, ['data_type' => 'html']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => [
        'html' => [
            ['_lang' => 'en-us'],
            [
                'head' => [
                    [
                        'meta' => [
                            ['_charset' => 'utf-8'],
                        ],
                    ], [
                        'title' => [
                            ['#text' => 'HTML generated by ApiOpenStudio'],
                        ],
                    ],
                ],
            ], [
                "body" => [
                    [
                        "div" => [
                            ["#text" => "true"],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

$I->wantTo('Test cast boolean true to image.');
$I->sendGet($uri, ['data_type' => 'image']);
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'code' => 6,
        'id' => 'test cast boolean cast',
        'message' => 'Cannot cast boolean to image.',
    ],
]);

$I->wantTo('Test cast boolean true to integer.');
$I->sendGet($uri, ['data_type' => 'integer']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => 1,
]);

$I->wantTo('Test cast boolean true to json.');
$I->sendGet($uri, ['data_type' => 'json']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => true,
]);

$I->wantTo('Test cast boolean true to text.');
$I->sendGet($uri, ['data_type' => 'text']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => 'true',
]);

$I->wantTo('Test cast boolean true to xml.');
$I->sendGet($uri, ['data_type' => 'xml']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => [
        'apiOpenStudioWrapper' => [
            'item' => 'true',
        ],
    ],
]);

$I->tearDownTestFromYaml($yamlFilename);

$I->comment('Testing with boolean false');
$yamlFilename = 'castBooleanFalse.yaml';
$uri = $I->getMyBaseUri() . '/cast/boolean';
$I->createResourceFromYaml($yamlFilename);

$I->wantTo('Test cast boolean false to array.');
$I->sendGet($uri, ['data_type' => 'array']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => [false],
]);

$I->wantTo('Test cast boolean false to boolean.');
$I->sendGet($uri, ['data_type' => 'boolean']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => false,
]);

$I->wantTo('Test cast boolean false to empty.');
$I->sendGet($uri, ['data_type' => 'undefined']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => null,
]);

$I->wantTo('Test cast boolean false to float.');
$I->sendGet($uri, ['data_type' => 'float']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => 0.0,
]);

$I->wantTo('Test cast boolean false to html.');
$I->sendGet($uri, ['data_type' => 'html']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => [
        'html' => [
            ['_lang' => 'en-us'],
            [
                'head' => [
                    [
                        'meta' => [
                            ['_charset' => 'utf-8'],
                        ],
                    ], [
                        'title' => [
                            ['#text' => 'HTML generated by ApiOpenStudio'],
                        ],
                    ],
                ],
            ], [
                "body" => [
                    [
                        "div" => [
                            ["#text" => "false"],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

$I->wantTo('Test cast boolean false to image.');
$I->sendGet($uri, ['data_type' => 'image']);
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'code' => 6,
        'id' => 'test cast boolean cast',
        'message' => 'Cannot cast boolean to image.',
    ],
]);

$I->wantTo('Test cast boolean false to integer.');
$I->sendGet($uri, ['data_type' => 'integer']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => 0,
]);

$I->wantTo('Test cast boolean false to json.');
$I->sendGet($uri, ['data_type' => 'json']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => false,
]);

$I->wantTo('Test cast boolean false to text.');
$I->sendGet($uri, ['data_type' => 'text']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => 'false',
]);

$I->wantTo('Test cast boolean false to xml.');
$I->sendGet($uri, ['data_type' => 'xml']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'ok',
    'data' => [
        'apiOpenStudioWrapper' => [
            'item' => 'false',
        ],
    ],
]);

$I->tearDownTestFromYaml($yamlFilename);
