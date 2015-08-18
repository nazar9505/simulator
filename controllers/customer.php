<?php

Class Controller_Customer Extends Controller_Base 
{
	public $layouts = "first_layouts";
	
	function index() 
	{
		$team_model = new Model_Teams();
		$data['teams'] = $team_model->getAllRows();

		$this->template->vars('data', $data);
		$this->template->view('index');
	}

	function orders()
	{
		$select = array('where' => 'type = '.ORDER);
		$element_model = new Model_Elements($select);
		$data['orders'] = $element_model->getAllRows();

		if(!empty($data['orders']))
		{
			foreach ($data['orders'] as $key => $order)
			{
				$data['orders'][$key]['barcode'] = '/ext/barcode/barcode.php?text='.$order['id'].'&size=40';
			}
		}

		$this->template->vars('data', $data);
		$this->template->view('orders');
	}

	function order($args)
	{
		$id = $args[0];
		$element_model = new Model_Elements();
		$order = $element_model->getRowById($id);

		if (isset($order))
		{
			$order['barcode'] = '/ext/barcode/barcode.php?text='.$order['id'].'&size=40';

			$this->layouts = 'order_layouts';
			$this->template->layouts = 'order_layouts';

			$this->template->vars('order', $order);
			$this->template->view('order');
		}
	}

	function team($args)
	{
		$team_id = $args[0];
		
		$team_model = new Model_Teams();
		$team = $team_model->getRowById($team_id);

		if (isset($team))
		{
			$select = array('where' => 'team_id = '.$team_id);
			$operation_model = new Model_Operations($select);
			$operations = $operation_model->getAllRows();

			$orders = array();
			if (!empty($operations))
			{
				foreach ($operations as $key => $operation)
				{
					$select = array('where' => 'id = '.$operation['element_id'].' and type = '.ORDER);
					$element_model = new Model_Elements($select);
					$team['orders'] = $element_model->getAllRows();
				}
			}

			$this->template->vars('team', $team);
			$this->template->view('team');
		}
	}
}