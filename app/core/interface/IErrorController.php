<?php
namespace DevNoKage\Interface;

interface IErrorController {
    public function _404() : void;
    public function _500() : void;
    public function _502() : void;
}