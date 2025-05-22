<?php
namespace src\exceptions;;

use Psr\Container\NotFoundExceptionInterface;
class NotFoundException extends \Exception implements NotFoundExceptionInterface{}