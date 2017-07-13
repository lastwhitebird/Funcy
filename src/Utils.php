<?php

namespace LWB\Functional;

class Utils
{

	/**
	 * Functional functions for PHP.
	 *
	 * Please see accompanying README for more information.
	 *
	 * @copyright Kim Burgestrand
	 * @license X11 license (MIT license)
	 *          @homepage http://github.com/Burgestrand/Funcy
	 */
	
	/**
	 * Function composition
	 *
	 * Example (pseudocode)
	 * --------------------
	 * fa(x): return x + "a"
	 * fb(y): return y + "b"
	 * fc(z): return z + "c"
	 *
	 * print sequence(fa, fb, fc)('❤')
	 * // prints: ❤cba
	 *
	 * GOTCHA: Errors triggered by non-existing functions will have their
	 * index in reverse order: `arg#0` is actually the last argument. This
	 * is due to the fact that `compose` is defined using `sequence`.
	 *
	 * @param
	 *        	callback₁, callback₂, … callbackᵪ
	 * @return closure ∫(callback₁(callback₂(callbackᵪ(…))))
	 */
	public static function compose()
	{
		$fns = func_get_args();
		return call_user_func_array([
				__CLASS__,
				'sequence' 
		], array_reverse($fns));
	}

	/**
	 * Function composition in sequence
	 *
	 * Example (pseudocode)
	 * --------------------
	 * fa(x): return x + "a"
	 * fb(y): return y + "b"
	 * fc(z): return z + "c"
	 *
	 * print sequence(fa, fb, fc)('❤')
	 * // prints: ❤abc
	 *
	 * @param
	 *        	callback₁, callback₂, … callbackᵪ
	 * @return closure ∫(callbackᵪ(callback₂(callback₁(…))))
	 */
	public static function sequence()
	{
		$fns = func_get_args();
		
		foreach ($fns as $i => $fn)
		{
			if (!is_callable($fn, FALSE, $name))
			{
				trigger_error("All arguments must be valid callbacks (arg#{$i}: “${name}” failed)", E_USER_ERROR);
			}
		}
		
		return function () use ($fns)
		{
			$args = func_get_args();
			$acc = call_user_func_array(array_shift($fns), $args);
			
			foreach ($fns as $fn)
			{
				$acc = $fn($acc);
			}
			
			return $acc;
		};
	}

	/**
	 * Execute the callback on each element in the sequence
	 *
	 * Example (pseudocode)
	 * --------------------
	 * greet(name): return uppercase(name)
	 * names = 'Kim', 'Elin', 'Kelin'
	 *
	 * map(greet, names) // 'KIM', 'ELIN', 'KELIN'
	 *
	 * @see array_map
	 * @param
	 *        	callback
	 * @param
	 *        	Iterator
	 * @return array
	 */
	public static function map($fn, $iterable)
	{
		if (!is_callable($fn))
		{
			trigger_error('First argument must be a valid callback', E_USER_ERROR);
		}
		
		$result = array();
		
		foreach ($iterable as $key => $val)
		{
			$result[$key] = $fn($val);
		}
		
		return $result;
	}

	/**
	 * Executes the callback on `init` and the first element, then it executes
	 * the callback on the result of the previous execution and the second element
	 * and so on.
	 *
	 * Example (pseudocode)
	 * --------------------
	 * add(a, b): return a + b
	 * numbers = 0, 1, 2, 3, 4, 5
	 *
	 * print reduce(add, 0, numbers) // 15
	 *
	 * @param
	 *        	callback
	 * @param
	 *        	mixed init
	 * @param
	 *        	Iterator
	 * @return array
	 */
	public static function foldl($fn, $init, $iterable)
	{
		if (!is_callable($fn))
		{
			trigger_error('First argument must be a valid callback', E_USER_ERROR);
		}
		
		$acc = $init;
		
		foreach ($iterable as $x)
		{
			$acc = $fn($acc, $x);
		}
		
		return $acc;
	}

	public static function iter_reverse($iterable)
	{
		for (end($iterable); ($key = key($iterable)) !== null; prev($iterable))
		{
			yield $key => current($iterable);
		}
	}

	public static function foldr($fn, $init, $iterable)
	{
		if (!is_callable($fn))
		{
			trigger_error('First argument must be a valid callback', E_USER_ERROR);
		}
		
		$acc = $init;
		
		foreach (self::iter_reverse($iterable) as $x)
		{
			$acc = $fn($acc, $x);
		}
		
		return $acc;
	}

	/**
	 * Flips the (first two) arguments of a function.
	 *
	 * Example (pseudocode)
	 * --------------------
	 * pow(a, b): return a ** b
	 * wop: flip(pow)
	 *
	 * print wop(10, 2) // 1024
	 *
	 * @param
	 *        	callback
	 * @return closure
	 */
	public static function flip($fn)
	{
		return function () use ($fn)
		{
			$args = func_get_args();
			$a = array_shift($args);
			$b = array_shift($args);
			$args = array_merge(array(
					$b,
					$a 
			), $args);
			return call_user_func_array($fn, $args);
		};
	}

	/**
	 * Partial function application
	 *
	 * Example (pseudocode)
	 * --------------------
	 * fa(greet, name): return greet + ' ' + name
	 * fx = curry(fa, 'Hello')
	 *
	 * fx('Kim') // Hello Kim
	 * fx('Elin') // Hello Elin
	 *
	 * @param
	 *        	callback
	 * @param
	 *        	a1, a2…
	 * @return closure ∫(callback(a1, a2, …))
	 * 
	 * Replaced by reactphp/partial::bind (renamed to partial, partial_right)
	 * 
	 */
	public static function partial(callable $fn, ...$bound)
	{
		return function (...$args) use ($fn, $bound)
		{
			return $fn(...self::mergeLeft($bound, $args));
		};
	}

	public static function partial_right(callable $fn, ...$bound)
	{
		return function (...$args) use ($fn, $bound)
		{
			return $fn(...self::mergeRight($bound, $args));
		};
	}

	/**
	 *
	 * @return Placeholder
	 */
	public static function …()
	{
		return Placeholder::getInstance();
	}

	/**
	 *
	 * @return Placeholder
	 */
	public static function placeholder()
	{
		return …();
	}

	/**
	 *
	 * @internal
	 */
	public static function mergeLeft(array $stored, array $invoked)
	{
		self::resolvePlaceholder($stored, $invoked);
		return array_merge($stored, $invoked);
	}

	/**
	 *
	 * @internal
	 */
	public static function mergeRight(array $stored, array $invoked)
	{
		self::resolvePlaceholder($stored, $invoked);
		return array_merge($invoked, $stored);
	}

	/**
	 *
	 * @internal
	 */
	public static function resolvePlaceholder(array &$stored, array &$invoked)
	{
		foreach ($stored as $position => &$param)
		{
			if ($param instanceof Placeholder)
			{
				$param = $param->resolve($invoked, $position);
			}
		}
	}

	/* End of file funcy.php */
	/*
	 * curry by Andrew Lelechenko
	 */
	public static function curry($callback, $args = [])
	{
		$num = (new \ReflectionFunction($callback))->getNumberOfRequiredParameters();
		return function (...$invoked) use ($callback, $args, $num)
		{
			$args = array_merge($args, $invoked);
			if (count($args) >= $num)
				return $callback(...$args);
			else
				return self::curry($callback, $args, $num);
		};
	}

	/*
	 * uncurry by LWB 
	 */
	public static function uncurry($callback)
	{
		return function (...$invoked) use ($callback)
		{
			return self::foldl('call_user_func', $callback, $invoked);
		};
	}
}