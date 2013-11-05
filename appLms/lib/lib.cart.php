<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class Learning_Cart
{
	public function  __construct()
	{
		Learning_Cart::istance();
	}

	public function init()
	{
		if(!isset($_SESSION['lms_cart']))
			$_SESSION['lms_cart'] = array();
	}

	public function cartItemCount()
	{
		$count = 0;
		$cart = $_SESSION['lms_cart'];$i = 0;

		foreach($cart as $id_course => $extra)
		{
			if(is_array($extra))
			{
				if(isset($extra['classroom']))
					$count += count($extra['classroom']);
				else
					$count += count($extra['edition']);
			}
			else
				$count++;
		}

		if($count == 0)
			Learning_Cart::emptyCart();

		return $count;
	}

	public function emptyCart()
	{
		$_SESSION['lms_cart'] = array();
	}
}

?>