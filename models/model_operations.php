<?php

Class Model_Operations Extends Model_Base {
	
	public $id;
	public $date_time;
	public $team_id;
	public $customer_id;
	public $provider_id;
	public $element_id;
	public $residue;
	public $price;
	public $period_id;
	public $name;
	public $type;
	public $state;
	
	public function fieldsTable(){
		return array(		
			'id' => 'Id',
			'date_create' => 'Дата создания',
			'team_id' => 'Id команды',
			'customer_id' => 'Id заказчика',
			'provider_id' => 'Id поставщика',
			'element_id' => 'Id элемента',
			'residue' => 'Остаток',
			'price' => 'Цена',
			'period_id' => 'Id периода',
			'name' => 'Id периода',
			'type' => 'Id периода',
			'state' => 'Id периода'
		);
	}
	
}