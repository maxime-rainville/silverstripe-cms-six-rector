<?php
namespace App;

use SilverStripe\Control\HTTPRequest;

class Test {
    function boom() {
        $httpMethod = 'GET';
        $url = '/hello-world';
        $getVars = ['ID' => 123];
        $postVars = ['password' => 'helloworld'];
        $body = "raw data";

        $request = new HTTPRequest($httpMethod, $url, $getVars, $postVars, $body);
        $request = new HTTPRequest($httpMethod, $url, [], [], $body);
        $request = new HTTPRequest($httpMethod, $url, $getVars);
        $request = new HTTPRequest($httpMethod, $url, [], $postVars);
        $request = new HTTPRequest($httpMethod, $url);
        $request = new HTTPRequest($httpMethod, $url, [], []);
    }
}

-----
<?php
namespace App;

use SilverStripe\Control\HTTPRequest;

class Test {
    function boom() {
        $httpMethod = 'GET';
        $url = '/hello-world';
        $getVars = ['ID' => 123];
        $postVars = ['password' => 'helloworld'];
        $body = "raw data";

        $request = new HTTPRequest($httpMethod, $url, $getVars, $postVars, $body);
        $request = new HTTPRequest($httpMethod, $url, [], [], $body);
        $request = new HTTPRequest($httpMethod, $url, $getVars);
        $request = new HTTPRequest($httpMethod, $url, [], $postVars);
        $request = new HTTPRequest($httpMethod, $url);
        $request = new HTTPRequest($httpMethod, $url, [], []);
    }
}
