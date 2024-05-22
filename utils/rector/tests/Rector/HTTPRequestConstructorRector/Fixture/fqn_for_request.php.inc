<?php
namespace App;

class Test {
    function boom() {
        $httpMethod = 'GET';
        $url = '/hello-world';
        $getVars = ['ID' => 123];
        $postVars = ['password' => 'helloworld'];
        $body = "raw data";

        $request = new \SilverStripe\Control\HTTPRequest($httpMethod, $url, $getVars, $postVars, $body);
    }
}

-----
<?php
namespace App;

class Test {
    function boom() {
        $httpMethod = 'GET';
        $url = '/hello-world';
        $getVars = ['ID' => 123];
        $postVars = ['password' => 'helloworld'];
        $body = "raw data";

        $request = \SilverStripe\Control\HTTPRequest::create($httpMethod, $url, $getVars, $postVars, $body);
    }
}
