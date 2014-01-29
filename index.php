<?php
/*
 * Copyright 2013 Robert Stoll <rstoll@tutteli.ch>
 * Licensed under the Apache License, Version 2.0 (the "License");
 * See file "LICENSE" in the root directory for more information.
 *
 */
namespace ch\tutteli;
 
include __DIR__.'\TypedArray.php';

function test($fnc) {
    try {
        $fnc();
    } catch (TypeException $ex) {
        echo '<div style="border:1px dashed #F00; padding:10px; background-color:#FDB0C1;margin-top:5px;margin-bottom:5px;">'.
        '<span style="font-weight:bold">'.$ex->getMessage().'</span><br/>'.\str_replace("\n",'<br/>',$ex->getTraceAsString()).'</div>';
    }
}
echo 'TypedArray of type bool:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_BOOL);
            $tarr[] = true;
            $tarr[] = false;

            $tarr[] = 1;
        });

echo 'TypedArray of type int:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_INT);
            $tarr[] = 1;
            $tarr[] = 2;

            $tarr[] = 3.0;
        });
        
echo 'TypedArray of type float:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_FLOAT);
            $tarr[] = 1.2;
            $tarr[] = 2.4;

            $tarr[] = 'a';
        });

echo 'TypedArray of type string:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_STRING);
            $tarr[] = 'a';
            $tarr[] = "b";

            $tarr[] = 1;
        });

echo 'TypedArray of type resource:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_RESOURCE);
            $tarr[] = fopen('TypedArray.php', 'r');

            $tarr[] = 'a';
        });
        
echo 'TypedArray of type callable:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_CALLABLE);
            $tarr[] = array($tarr, "isValid");
            $tarr[] = array($tarr, "getTypeAsInt");

            $tarr[] = 'a';
        });
        
echo 'TypedArray of type class/interface:<br/>';
test(function() {
            $tarr = new TypedArray(Types::T_CLASS_OR_INTERFACE, 'TypedArray');
            $tarr[] = new TypedArray(Types::T_BOOL);
            $tarr[] = new TypedArray(Types::T_FLOAT);

            $tarr[] = 'a';
        });

echo '<br/><br/>type hint test';        
        
class Foo{}
class Bar{}
        
function foo(TypedArray $arr){
    $arr->checkIsSameType(Types::T_CLASS_OR_INTERFACE, "Foo");
}

test(function(){
    foo(new TypedArray(Types::T_BOOL));
});
test(function(){
    foo(new TypedArray(Types::T_CLASS_OR_INTERFACE, "NonExistingClass"));
});
test(function(){
    foo(new TypedArray(Types::T_CLASS_OR_INTERFACE, "Bar"));
});


echo 'Array initialisation:<br/>';

//initialisation of a TypedArray - almost like a normal array
$typedArray = TypedArray::createFromArray(Types::T_CLASS_OR_INTERFACE, [new Foo(), new Foo()], 'ch\tutteli\Foo');
var_dump($typedArray);
$typedArray = TypedArray::createFromArray(Types::T_CLASS_OR_INTERFACE, ['a' => new Foo(), 2 => new Foo()], 'ch\tutteli\Foo');
var_dump($typedArray);

echo 'Reference test:<br/>';

$typedArray = new TypedArray(Types::T_CLASS_OR_INTERFACE, 'ch\tutteli\Foo');
$foo = new Foo();
$typedArray[] = &$foo;
var_dump($typedArray);

?>
