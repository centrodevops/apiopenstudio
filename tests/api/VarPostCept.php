<?php
$I = new ApiTester($scenario);
$I->performLogin();
$I->setYamlFilename('varPost.yaml');
$I->createResourceFromYaml();

$I->wantTo('populate a varPost with text and see the result.');
$I->callResourceFromYaml(['value' => 'text']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('text');

$I->wantTo('populate a varPost with true and see the result.');
$I->callResourceFromYaml(['value' => 'true']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('true');

$I->wantTo('populate a varPost with 1.6 and see the result.');
$I->callResourceFromYaml(['value' => '1.6']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('1.6');

$I->wantTo('populate a varPost with 1.6 and see the result.');
$I->callResourceFromYaml(['value' => 1.6]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('1.6');

$I->wantTo('populate a varPost with 1 and see the result.');
$I->callResourceFromYaml(['value' => 1.0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('1');

$I->wantTo('populate a varPost with 1.0 and see the result.');
$I->callResourceFromYaml(['value' => 1]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('1');

$I->wantTo('populate a varPost with 0 and see the result.');
$I->callResourceFromYaml(['value' => 0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('0');

$I->wantTo('populate a varPost with 0.0 and see the result.');
$I->callResourceFromYaml(['value' => 0.0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('0');

$I->wantTo('populate a varPost with wrong varname and nullable true and see the result.');
$I->callResourceFromYaml(['values' => 'test', 'nullable' => true]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseEquals('');

$I->wantTo('populate a varPost with wrong varname and nullable false and see the result.');
$I->callResourceFromYaml(['values' => 'test', 'nullable' => false]);
$I->seeResponseCodeIs(417);
$I->seeResponseIsJson();
$I->seeResponseContains('Post variable (value) not received.');

$I->wantTo('populate a varPost with wrong varname and nullable not set and see the result.');
$I->callResourceFromYaml(['values' => 'test']);
$I->seeResponseCodeIs(417);
$I->seeResponseIsJson();
$I->seeResponseContains('Post variable (value) not received.');

$I->tearDownTestFromYaml();
