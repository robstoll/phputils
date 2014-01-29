<?php
/*
 * Copyright 2013 Robert Stoll <rstoll@tutteli.ch>
 * Licensed under the Apache License, Version 2.0 (the "License");
 * See file "LICENSE" in the root directory for more information.
 *
 */
namespace ch\tutteli;

class TypeException extends \Exception
{   
}

interface Types
{
    const T_BOOL = 1;
    const T_INT = 2;
    const T_FLOAT = 3;
    const T_STRING = 4;
    const T_ARRAY = 5;
    const T_RESOURCE = 6;
    const T_CALLABLE = 7;
    const T_CLASS_OR_INTERFACE = 8;

}

interface ITypeChecker
{

    function isValid($value);

    function getTypeAsString();
}

class BoolTypeChecker implements ITypeChecker
{
    function getTypeAsString() {
        return 'bool';
    }

    function isValid($value) {
        return \is_bool($value);
    }
}

class IntTypeChecker
{
    function getTypeAsString() {
        return 'int';
    }
    function isValid($value) {
        return \is_int($value);
    }
}

class FloatTypeChecker
{
    function getTypeAsString() {
        return 'float';
    }
    function isValid($value) {
        return \is_float($value);
    }
}

class StringTypeChecker
{
    function getTypeAsString() {
        return 'string';
    }
    function isValid($value) {
        return \is_string($value);
    }
}

class ArrayTypeChecker
{
    function getTypeAsString() {
        return 'array';
    }
    function isValid($value) {
        return \is_array($value);
    }
}

class ResourceTypeChecker
{
    function getTypeAsString() {
        return 'resource';
    }
    function isValid($value) {
        return \is_resource($value);
    }
}

class CallableTypeChecker
{
    function getTypeAsString() {
        return 'callable';
    }
    function isValid($value) {
        return \is_callable($value);
    }
}

class ObjectTypeChecker implements ITypeChecker
{
    private $classOrInterfaceName;
    public function __construct($classOrInterfaceName) {
        $this->classOrInterfaceName = $classOrInterfaceName;
    }
    function getTypeAsString() {
        return $this->classOrInterfaceName;
    }
    function isValid($value) {
        return \is_a($value, $this->classOrInterfaceName);
    }
}

class TypeCheckerFactory
{
    private static $bool;
    private static $int;
    private static $float;
    private static $string;
    private static $array;
    private static $resource;
    private static $callable;

    public static function build($typeAsInt, $classOrInterfaceName = null) {
        switch ($typeAsInt) {
            case Types::T_BOOL:
                if (self::$bool === null) {
                    self::$bool = new BoolTypeChecker();
                }
                return self::$bool;
            case Types::T_INT:
                if (self::$int === null) {
                    self::$int = new IntTypeChecker();
                }
                return self::$int;
            case Types::T_FLOAT:
                if (self::$float === null) {
                    self::$float = new FloatTypeChecker();
                }
                return self::$float;
            case Types::T_STRING:
                if (self::$string === null) {
                    self::$string = new StringTypeChecker();
                }
                return self::$string;
            case Types::T_ARRAY:
                if (self::$array === null) {
                    self::$array = new ArrayTypeChecker();
                }
                return self::$array;
            case Types::T_RESOURCE:
                if (self::$resource === null) {
                    self::$resource = new ResourceTypeChecker();
                }
                return self::$resource;
            case Types::T_CALLABLE:
                 if (self::$callable === null) {
                    self::$callable = new CallableTypeChecker();
                }
                return self::$callable;
            case Types::T_CLASS_OR_INTERFACE:
                if(!class_exists($classOrInterfaceName) && !interface_exists($classOrInterfaceName)){
                    throw new TypeException('Cannot create a TypedArray because the class/interface '.$classOrInterfaceName.' does not exist');    
                }
                return new ObjectTypeChecker($classOrInterfaceName);
            default:
                throw new TypeException('The given typeAsInt '.$typeAsInt.' is not supported');
        }
    }
}

class TypedArray implements \ArrayAccess, \Countable
{
    /* @var $typeChecker ITypeChecker */

    private $typeChecker;
    private $typeAsInt;
    private $arr;

    public static function createFromArray($typeAsInt, array $arr, $classOrInterfaceName = null) {
        $tarr = new TypedArray($typeAsInt, $classOrInterfaceName);
        foreach ($arr as $k => $v) {
            $tarr[$k] = $v;
        }
        return $tarr;
    }

    public function __construct($typeAsInt, $classOrInterfaceName = null) {
        $this->typeChecker = TypeCheckerFactory::build($typeAsInt, $classOrInterfaceName);
    }

    public function checkIsSameType($typeAsInt, $classOrInterfaceName = null) {
        $arrTypeAsInt = $this->GetTypeAsInt();
        if ($arrTypeAsInt !== $typeAsInt) {
            switch ($typeAsInt) {
                case Types::T_BOOL:
                    $type = 'bool';
                    break;
                case Types::T_INT:
                    $type = 'int';
                    break;
                case Types::T_FLOAT:
                    $type = 'float';
                    break;
                case Types::T_STRING:
                    $type = 'string';
                    break;
                case Types::T_ARRAY:
                    $type = 'array';
                    break;
                case Types::T_RESOURCE:
                    $type = 'resource';
                    break;
                case Types::T_CALLABLE:
                    $type = 'callable';
                    break;
                case Types::T_CLASS_OR_INTERFACE:
                    if($classOrInterfaceName === null){
                        throw new TypeException('The given TypedArray is of type '.$this->getTypeAsString().' and not of type class/interface ($classOrInterfaceName was missing, cannot provide a more precise error message)');
                    }
                    $type = $classOrInterfaceName;
                    break;
                default:
                    throw new TypeException('The given typeAsInt '.$typeAsInt.' is not supported');
            }
            throw new TypeException('The given TypedArray is of type '.$this->getTypeAsString().' and not of type '.$type);
        }else if($arrTypeAsInt == Types::T_CLASS_OR_INTERFACE){
            $type = $this->getTypeAsString();
            if($type != $classOrInterfaceName){
                throw new TypeException('The given TypedArray is of type '.$type.' and not of type '.$classOrInterfaceName);
            }
        }
    }

    public function isSameType($typeAsInt) {
        return $this->GetTypeAsInt() === $typeAsInt;
    }

    public function getTypeAsString() {
        return $this->typeChecker->getTypeAsString();
    }

    public function getTypeAsInt() {
        return $this->typeAsInt;
    }

    public function isValid($value) {
        return $this->typeChecker->isValid($value);
    }

    public function offsetSet($offset, $value) {
        if ($this->isValid($value)) {
            $arr[] = $value;
        } else {
            throw new TypeException('The given value was not of type '.$this->getTypeAsString().': '.var_export($value, true));
        }
    }

    public function offsetExists($offset) {
        return isset($this->arr[$offset]);
    }

    public function offsetGet($offset) {
        return $this->arr[$offset];
    }

    public function offsetUnset($offset) {
        $this->arr[$offset];
    }

    public function count() {
        return \count($this->arr);
    }
}
?>
