<?php



class Learning_Cart
{
	public function  __construct()
	{
		Learning_Cart::istance();
	}

	public function init()
	{
		if(!isset($_SESSION['lms_cart']))
			$_SESSION['lms_cart'] = [];
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
		$_SESSION['lms_cart'] = [];
	}
}

?>