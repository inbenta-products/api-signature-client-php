<?php

namespace Inbenta\ApiSignature\Test\PhpUnit;

use PHPUnit\Framework\TestCase;
use Inbenta\ApiSignature\SignatureClient;
use Inbenta\ApiSignature\Signers\Signer;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SignatureClientTest extends TestCase
{
    protected $API_BASE_URL = '';

    // sample data
    protected $fixtures = [
        [
            'testname' => 'url-no-query',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar/bG9nOjozOTUyMjEyNzg4MTk3NTk0NTU=',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar%2FbG9nOjozOTUyMjEyNzg4MTk3NTk0NTU%3D&1552647740&v1'
            ],
            'response' => [
                'body' => '{"total_count":1,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTUyMjEyNzg4MTk3NTk0NTU=","date":"2018-12-03T10:32:00+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A1%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTUyMjEyNzg4MTk3NTk0NTU%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222018-12-03T10%3A32%3A00%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-query',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=development,production',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22development%2Cproduction%22&1552647740&v1'
            ],
            'response' => [
                'body' => '{"total_count":5,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNDUyNjA2NTAyOTc0MDY=","date":"2019-01-09T16:36:39+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"development"},{"event_id":"bG9nOjozOTYwNDQ4NTQ5MzM4NDQ3MDg=","date":"2019-01-09T16:10:14+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"development"},{"event_id":"bG9nOjozOTYwNDQzNjU5NjIwMzA0OTU=","date":"2019-01-09T15:38:24+00:00","user_question":"flight change","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":10,"external":false}],"user_type":0,"env":"development"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A5%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNDUyNjA2NTAyOTc0MDY%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-09T16%3A36%3A39%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22development%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNDQ4NTQ5MzM4NDQ3MDg%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-09T16%3A10%3A14%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22development%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNDQzNjU5NjIwMzA0OTU%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-09T15%3A38%3A24%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight+change%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A10%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22development%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-more-complex-query',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=flight',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22production%22%26user_question%3D%22flight%22&1552647740&v1'
            ],
            'response' => [
                'body' =>  '{"total_count":2,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A2%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-query-with-spaces',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=flight offer',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22production%22%26user_question%3D%22flight%20offer%22&1552647740&v1'
            ],
            'response' => [
                'body' =>  '{"total_count":2,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A2%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-query-with-spaces-as-plus-sign',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=flight+offer',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22production%22%26user_question%3D%22flight%20offer%22&1552647740&v1'
            ],
            'response' => [
                'body' =>  '{"total_count":2,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A2%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-query-with-spaces-as-%20',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=flight%20offer',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22production%22%26user_question%3D%22flight%20offer%22&1552647740&v1'
            ],
            'response' => [
                'body' =>  '{"total_count":2,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A2%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-query-with-special-chars',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=pregunta en català',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22production%22%26user_question%3D%22pregunta%20en%20catal%5Cu00e0%22&1552647740&v1'
            ],
            'response' => [
                'body' =>  '{"total_count":2,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A2%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-query-with-special-chars-already-encoded',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar?date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=pregunta%20en%20catal%C3%A0',
                'body' => '',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D%222019-01-01%22%26date_to%3D%222019-01-31%22%26env%3D%22production%22%26user_question%3D%22pregunta%20en%20catal%5Cu00e0%22&1552647740&v1'
            ],
            'response' => [
                'body' =>  '{"total_count":2,"offset":0,"length":1000,"results":[{"event_id":"bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM=","date":"2019-01-10T09:38:13+00:00","user_question":"How can I book a flight?","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":30,"external":false},{"id_content":1,"external":false},{"id_content":13,"external":false},{"id_content":16,"external":false}],"user_type":0,"env":"production"},{"event_id":"bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI=","date":"2019-01-10T09:38:06+00:00","user_question":"flight","log_type":"SEARCH","has_matching":true,"matchings":[{"id_content":25,"external":false},{"id_content":4,"external":false},{"id_content":32,"external":false},{"id_content":1,"external":false},{"id_content":37,"external":false}],"user_type":0,"env":"production"}]}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22total_count%5C%22%3A2%2C%5C%22offset%5C%22%3A0%2C%5C%22length%5C%22%3A1000%2C%5C%22results%5C%22%3A%5B%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTE4MzQ1MTA4ODM%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A13%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22How+can+I+book+a+flight%3F%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A30%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A13%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A16%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%2C%7B%5C%22event_id%5C%22%3A%5C%22bG9nOjozOTYwNjA5NTAyNjMwMDY5OTI%3D%5C%22%2C%5C%22date%5C%22%3A%5C%222019-01-10T09%3A38%3A06%2B00%3A00%5C%22%2C%5C%22user_question%5C%22%3A%5C%22flight%5C%22%2C%5C%22log_type%5C%22%3A%5C%22SEARCH%5C%22%2C%5C%22has_matching%5C%22%3Atrue%2C%5C%22matchings%5C%22%3A%5B%7B%5C%22id_content%5C%22%3A25%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A4%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A32%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A1%2C%5C%22external%5C%22%3Afalse%7D%2C%7B%5C%22id_content%5C%22%3A37%2C%5C%22external%5C%22%3Afalse%7D%5D%2C%5C%22user_type%5C%22%3A0%2C%5C%22env%5C%22%3A%5C%22production%5C%22%7D%5D%7D%22'
            ]
        ],
        [
            'testname' => 'url-with-body',
            'signature_key' => 'my-signature-key',
            'signature_version' => 'v1',
            'timestamp' => 1552647740,
            'request' => [
                'url' => 'v1/foo/bar',
                'body' => 'date_from=2019-01-01&date_to=2019-01-31&env=production&user_question=flight',
                'base_string' => 'GET&v1%2Ffoo%2Fbar&date_from%3D2019-01-01%26date_to%3D2019-01-31%26env%3Dproduction%26user_question%3Dflight&1552647740&v1'
            ],
            'response' => [
                'body' => '{"error":{"message":"Signature provided is not valid","code":403}}',
                'base_string' => 'v1&1552647740&%22%7B%5C%22error%5C%22%3A%7B%5C%22message%5C%22%3A%5C%22Signature+provided+is+not+valid%5C%22%2C%5C%22code%5C%22%3A403%7D%7D%22'
            ]
        ]
    ];

    public function setUp()
    {
        if (!file_exists(__DIR__.'/../api_config.php')) {
            $this->markTestSkipped('Missing api_config.php file in /test directory. Create one first to run the test suite');
        }
        $config = include __DIR__.'/../api_config.php';
        $this->API_BASE_URL = $config['API_BASE_URL'];
    }

    public function testGenerateRequestSignature()
    {
        // test valid signature with url
        foreach ($this->fixtures as $test) {
            $client = new SignatureClient(
                $this->API_BASE_URL,
                $test['signature_key'],
                $test['signature_version']
            );
            $expectedSignature = hash_hmac(
                Signer::HASH_ALGORITHM,
                $test['request']['base_string'],
                $test['signature_key']
            );
            $signature = $client->generateRequestSignature(
                $test['request']['url'],
                $test['request']['body'],
                'GET',
                $test['timestamp']
            );
            $this->assertEquals($signature, $expectedSignature, "Error in test {$test['testname']}:");
        }
    }

    public function testValidateResponseSignature()
    {
        // test valid response signatures
        foreach ($this->fixtures as $test) {
            $client = new SignatureClient(
                $this->API_BASE_URL,
                $test['signature_key'],
                $test['signature_version'],
                $test['timestamp']
            );
            $expectedSignature = hash_hmac(
                Signer::HASH_ALGORITHM,
                $test['response']['base_string'],
                $test['signature_key']
            );
            $isValid = $client->validateResponseSignature($expectedSignature, $test['response']['body']);
            $this->assertTrue($isValid, "Error in test {$test['testname']}:");
        }
    }

    public function testSignRequest()
    {
        // test valid request signatures
        foreach ($this->fixtures as $test) {
            $client = new SignatureClient(
                $this->API_BASE_URL,
                $test['signature_key'],
                $test['signature_version']
            );
            $expectedSignature = hash_hmac(
                Signer::HASH_ALGORITHM,
                $test['request']['base_string'],
                $test['signature_key']
            );
            $request = new Request('GET', $test['request']['url'], [], $test['request']['body']);
            $request = $client->signRequest($request, $test['timestamp']);
            $signatureVersion = $request->getHeaderLine(SignatureClient::SIGNATURE_VERSION_HEADER);
            $timestamp = $request->getHeaderLine(SignatureClient::TIMESTAMP_HEADER);
            $signature = $request->getHeaderLine(SignatureClient::SIGNATURE_HEADER);
            $this->assertEquals($signatureVersion, $client->getSignatureVersion());
            $this->assertEquals($timestamp, $client->getTimestamp());
            $this->assertEquals($signature, $expectedSignature, "Error in test {$test['testname']}:");
        }
    }

    public function testValidateResponse()
    {
        // test valid response signatures
        foreach ($this->fixtures as $test) {
            $client = new SignatureClient(
                $this->API_BASE_URL,
                $test['signature_key'],
                $test['signature_version'],
                $test['timestamp']
            );
            $expectedSignature = hash_hmac(
                Signer::HASH_ALGORITHM,
                $test['response']['base_string'],
                $test['signature_key']
            );
            $response = new Response(
                200,
                [ SignatureClient::SIGNATURE_HEADER => $expectedSignature ],
                $test['response']['body']
            );
            $isValid = $client->validateResponse($response);
            $this->assertTrue($isValid, "Error in test {$test['testname']}:");
        }
    }

    /**
     * @expectedException Inbenta\ApiSignature\Exceptions\SignatureClientException
     * @expectedExceptionMessage Invalid URL
     */
    public function testInvalidURLInitialization()
    {
        $client = new SignatureClient(
            'invalid_url',
            'my_signature_key'
        );
        $request = new Request('GET', 'invalid_url/invalid_end_point');
        $request = $client->signRequest($request);
    }

    /**
     * @expectedException Inbenta\ApiSignature\Exceptions\SignatureClientException
     * @expectedExceptionMessage Signature Key required
     */
    public function testEmptySignatureKeyInitialization()
    {
        $client = new SignatureClient(
            $this->API_BASE_URL,
            ''
        );
        $request = new Request('GET', 'sample_url/sample_end_point');
        $request = $client->signRequest($request);
    }

    /**
     * @expectedException Inbenta\ApiSignature\Exceptions\SignatureClientException
     * @expectedExceptionMessage RequestSigner version v2 not implemented
     */
    public function testNotImplementedSignatureVersion()
    {
        $client = new SignatureClient(
            $this->API_BASE_URL,
            'my_signature_key',
            'v2'
        );
        $request = new Request('GET', 'sample_url/sample_end_point');
        $request = $client->signRequest($request);
    }
}
