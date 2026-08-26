--TEST--
Object test, unserialize_callback_func (empty value, callback changing the setting)
--SKIPIF--
<?php if (PHP_VERSION_ID < 80600) echo "skip requires php >= 8.6\n"; ?>
--INI--
error_reporting=E_ALL
unserialize_callback_func=
--FILE--
<?php
if(!extension_loaded('igbinary')) {
	dl('igbinary.' . PHP_SHLIB_SUFFIX);
}

// TODO undo workaround when #[AllowDynamicProperties] is added to __PHP_Incomplete_Class
error_reporting(E_ALL & ~E_DEPRECATED);

// An empty unserialize_callback_func means "no callback", it must not be
// called as if it were a function name.
var_dump(ini_get('unserialize_callback_func'));
var_dump(get_class(igbinary_unserialize(pack('H*', '0000000217034f626a140211016106011101620602'))));

// The callback is free to change unserialize_callback_func while it runs.
// The warning has to name the callback that was actually called.
function reassigning_autoload($classname) {
	echo "Called for $classname\n";
	ini_set('unserialize_callback_func', 'some_other_callback');
}

ini_set('unserialize_callback_func', 'reassigning_autoload');
var_dump(get_class(igbinary_unserialize(pack('H*', '0000000217034f626b140211016106011101620602'))));
?>
--EXPECTF--
string(0) ""
string(22) "__PHP_Incomplete_Class"
Called for Obk

Warning: igbinary_unserialize(): Function reassigning_autoload() hasn't defined the class it was called for in %sigbinary_102.php on line %d
string(22) "__PHP_Incomplete_Class"
