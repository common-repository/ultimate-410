<?php

namespace TinyWeb\Ultimate410;

class RuleTester
{
    private $regex;
    private $rule;

    public function __construct(\stdClass $obj)
    {
        $this->regex = (bool)$obj->regex;
        $this->rule  = $obj->request;
    }

    public function test($request)
    {
        if ($this->regex) {
            return (bool)(preg_match($this->rule, $request) ?: preg_match($this->rule, urldecode($request)));
        }

        if (str_contains($this->rule, "'")) {
            $this->rule = preg_replace("/((?<!\\\)')/", '\\\'', $this->rule);
        }

        return strcasecmp($request, $this->rule) === 0
               || strcasecmp(urldecode($request), $this->rule) === 0
               || strcasecmp(urldecode($request), urldecode(Plugin::sanitize($this->rule))) === 0;
    }
}
