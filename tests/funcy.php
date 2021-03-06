<?php
  require '../src/Utils.php';
  require '../src/Placeholder.php';
  require './asserts.php';
  
  use LWB\Functional\Utils as FN;

  // Helper functions
  function println($str)
  {
    echo $str . PHP_EOL;
  }
  
  // Testing functions
  function test_compose()
  {
    $a = function($x) { return $x . 'a'; };
    $b = function($x) { return $x . 'b'; };
    $c = function($x) { return $x . 'c'; };
    $fn = FN::compose($a, $b, $c);
    
    return is_identical($fn('d'), 'dcba');
  }
  
  function test_sequence()
  {
    $a = function($x) { return $x . 'a'; };
    $b = function($x) { return $x . 'b'; };
    $c = function($x) { return $x . 'c'; };
    $fn = FN::sequence($a, $b, $c);
    
    return is_identical($fn('d'), 'dabc');
  }
  
  function test_partial()
  {
  	$a = function($a, $b) { return $a ** $b; };
  	$pow2 = FN::partial($a, 2);
  	
  	return is_identical($pow2(10), 1024);
  }
  
  function test_partial_right()
  {
  	$a = function($a, $b) { return $a ** $b; };
  	$cube = FN::partial_right($a, 3);
  	
  	return is_identical($cube(10), 1000);
  }
  
  function test_map()
  {
    $a = function($x) { return $x * $x; };
    $range = range(0, 10);
    
    return is_identical(FN::map($a, $range), array_map($a, $range));
  }
  
  function test_reduce()
  {
    $a = function($acc, $x) { return $x ** $acc; };
    $range = range(1, 4);
    
    return is_identical(FN::foldl($a, 0, $range), 262144); // 4 ** 3 ** 2 ** 1 Exponential factorial
  }

  function test_reduce_right()
  {
  	$a = function($acc, $x) { return $x ** $acc; };
  	$range = range(3, 4);
  	
  	return is_identical(FN::foldr($a, 1, $range), 81); // 3 ** 4 ** 1 (pow is right-associative)
  }
  
  function test_flip()
  {
    $a = function($a, $b) { return pow($a, $b); };
    $b = function($a, $b, $c) { return $a . $b . $c; };
    
    $af = FN::flip($a);
    $bf = FN::flip($b);
    
    return is_identical($af(10, 2), $a(2, 10))
        && is_identical($bf('a', 'b', 'c'), $b('b', 'a', 'c'));
  }

  function test_curry()
  {
  	$str_replace=FN::curry('str_replace');
  	$f1=$str_replace('a');
  	$f2=$f1('Z');
  	$f3=$f2('asdfasdfasdf');
 	
  	return is_identical($f3, 'ZsdfZsdfZsdf'); 
  }
  
  function test_uncurry()
  {
  	$str_replace=FN::curry('str_replace');
  	$str_uncurry=FN::uncurry($str_replace);
  	
  	return is_identical($str_uncurry('-','.','1970-01-01'), '1970.01.01');
  }
  
  // Extract tests and run!
  $functions = get_defined_functions();
  $functions = $functions['user'];
  $functions = preg_grep('/^test_/', $functions);
  
  // How many tests succeded/failed
  $success = array();
  
  println('== Starting tests ==');
  println('');
  
  // Run the tests!
  foreach ($functions as $function)
  {
    println("[$function]");
    try
    {
      $function();
    }
    catch (Exception $e)
    {
      println("ERROR");
      println($e->getMessage());
      $success[$function] = FALSE;
      println('');
      continue;
    }
    
    $success[$function] = TRUE;
    println("success!");
    println('');
  }
  
  $nays = array_filter($success, function($x) { return ! $x; });
  $nnays = count($nays);
  $total = count($functions);
  
  echo "{$nnays} failed tests out of {$total} tests";
  
/* End of file funcy.php */
/* Location: ./tests/funcy.php */ 