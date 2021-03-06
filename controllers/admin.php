<?php

Class Controller_Admin Extends Controller_Base 
{
	public $layouts = "first_layouts";
	
	function index() 
	{
		$team_model = new Model_Teams();
		$data['teams'] = $team_model->getAllRows();

		if (!empty($data['teams']))
		{
			foreach ($data['teams'] as $key => $team)
			{
				$select = array(
					'where' => 'team_id = '.$team['id'],
					'order' => 'id'
				); 
				$operation_model = new Model_Operations($select);
				$operation = $operation_model->getLastRow();

				if (isset($operation) && !empty($operation))
				{
					$data['teams'][$key]['operation'] = $operation;
				}
			}
		}
		$select = array('where' => 'type = '.COST);
		$model = new Model_Elements($select);
		$data['costs'] = $model->getAllRows();

		$select = array('where' => 'type = '.FINE);
		$model = new Model_Elements($select);
		$data['fines'] = $model->getAllRows();

		$select = array('where' => "id = 'fine_time'");
		$game_model = new Model_Game($select);
		$game = $game_model->getOneRow();

		$data['fine_time'] = $game;

		$select = array('where' => "id = 'period_time'");
		$game_model = new Model_Game($select);
		$game = $game_model->getOneRow();

		$data['period_time'] = $game;

		$select = array('where' => "id = 'credit_rate'");
		$game_model = new Model_Game($select);
		$game = $game_model->getOneRow();

		$data['credit_rate'] = $game;

		$select = array('where' => "id = 'default_score'");
		$game_model = new Model_Game($select);
		$game = $game_model->getOneRow();

		$data['default_score'] = $game;

		$select = array('where' => "id = ".SKILL1);
		$skill_model = new Model_Skills($select);
		$skill = $skill_model->getOneRow();

		$data['salary_trainee'] = $skill;

		$select = array('where' => "id = ".SKILL2);
		$skill_model = new Model_Skills($select);
		$skill = $skill_model->getOneRow();

		$data['salary_master'] = $skill;

		$select = array('where' => "id = ".SKILL3);
		$skill_model = new Model_Skills($select);
		$skill = $skill_model->getOneRow();

		$data['salary_prof'] = $skill;

		$this->template->vars('data', $data);
		$this->template->view('index');
	}

	function add_team()
	{			$select = array('where' => "id = 'default_score'");		$game_model = new Model_Game($select);		$game = $game_model->getOneRow();				
		$team = new Model_Teams();
		$team->score = $game['value'];
		$team->credit = 0;
		$team->save();

		$team = new Model_Teams();
		$team_id = $team->getLastRow()['id'];

		$director = new Model_Users();
		$director->login = 'team'.$team_id;
		$crypt_pass = generate_password(10);
		$director->pass = $crypt_pass;
		$director->team_id = $team_id;
		$director->save();

		$staffname_model = new Model_Staffnames();
		$staffnames = $staffname_model->getAllRows();

		foreach ($staffnames as $key => $staffname)
		{
			$staff_model = new Model_Staffs();
			$staff_model->team_id = $team_id;
			$staff_model->name = $staffname['name'];
			$staff_model->skill_id = SKILL1;
			$staff_model->save();
		}

		$this->redirectToAction("team/$team_id");
	}

	function delete_team()
	{
		$team_id = $_POST['team_id'];

		$team_model = new Model_Teams();
		$select = array('where' => 'id = '.$team_id);
		$team_model->deleteBySelect($select);

		$this->redirectToAction('index');
	}


	function team($args)
	{
		$team_id = $args[0];
		if($team_id)
		{
			$model = new Model_Teams();
			$team = $model->getRowById($team_id);

			if (isset($team) && !empty($team))
			{	
				$select = array('where' => 'team_id = '.$team_id);
				$operation_model = new Model_Operations($select);
				$operations = $operation_model->getAllRows();

				if (!empty($operations))
				{
					foreach ($operations as $key => $operation)
					{
						$date = new DateTime($operation['date_time']);
						$operation['date_time'] = $date; 
						$team['operations'][] = $operation;
					}
				}
				$select = array('where' => 'type = '.COST);
				$model = new Model_Elements($select);
				$team['costs'] = $model->getAllRows();

				$select = array('where' => 'type = '.FINE);
				$model = new Model_Elements($select);
				$team['fines'] = $model->getAllRows();

				$select = array('where' => 'team_id = '.$team['id']);
				$user_model = new Model_Users($select);
				$user = $user_model->getOneRow();
				$team['user'] = $user;

				$data['price_parts'] = 0;
				$data['price_incomes'] = 0;
				$data['price_repayment'] = 0;
				$data['price_orders'] = 0;

				if (!empty($team['operations']))
				{
					foreach ($team['operations'] as $key => $operation)
					{
						if ($operation['type'] == PART)
						{
							$data['price_parts'] += $operation['price'];
						}
						if ($operation['type'] == INCOME)
						{
							$data['price_incomes'] += $operation['price'];
						}
						if ($operation['type'] == REPAYMENT)
						{
							$data['price_repayment'] += $operation['price'];
						}
						if ($operation['type'] == ORDER)
						{
							$data['price_orders'] += $operation['price'];
						}
					}	
				}

				$this->template->vars('team', $team);
				$this->template->vars('data', $data);
				$this->template->view('team');
			}
		}
	}

	function elements()
	{
		$select = array('where' => 'type = '.FINE);
		$element_model = new Model_Elements($select);
		$data['fines'] = $element_model->getAllRows();

		$select = array('where' => 'type = '.COST);
		$element_model = new Model_Elements($select);
		$data['costs'] = $element_model->getAllRows();

		$this->template->vars('data', $data);
		$this->template->view('elements');
	}

	function elements2()
	{
		$select = array('where' => 'type = '.ORDER);
		$element_model = new Model_Elements($select);
		$data['orders'] = $element_model->getAllRows();

		if (!empty($data['orders']))
		{
			foreach ($data['orders'] as $key => $order)
			{
				$select = array('where' => 'element_id = '.$order['id']);
				$operation_model = new Model_Operations($select);
				$operation = $operation_model->getOneRow();

				if (isset($operation))
				{
					$team_model = new Model_Teams();
					$team = $team_model->getRowById($operation['team_id']);
					$data['orders'][$key]['team'] = $team;
				}
			}
		}

		$select = array('where' => 'type = '.PART);
		$element_model = new Model_Elements($select);
		$data['parts'] = $element_model->getAllRows();

		if (!empty($data['parts']))
		{
			foreach ($data['parts'] as $key => $part)
			{
				$select = array('where' => 'element_id = '.$part['id']);
				$operation_model = new Model_Operations($select);
				$operation = $operation_model->getOneRow();

				if (isset($operation))
				{
					$team_model = new Model_Teams();
					$team = $team_model->getRowById($operation['team_id']);

					$data['parts'][$key]['team'] = $team['name'];
				}
			}
		}

		$customer_model = new Model_Customers();
		$customers = $customer_model->getAllRows();

		$provider_model = new Model_Providers();
		$providers = $provider_model->getAllRows();

		$this->template->vars('data', $data);
		$this->template->vars('customers', $customers);
		$this->template->vars('providers', $providers);
		$this->template->view('elements2');
	}

	function elements3()
	{
		$select = array('where' => 'type = '.CUST_FINE);
		$element_model = new Model_Elements($select);
		$data['cust_fines'] = $element_model->getAllRows();

		$select = array('where' => 'type = '.PROM);
		$element_model = new Model_Elements($select);
		$data['proms'] = $element_model->getAllRows();

		$this->template->vars('data', $data);
		$this->template->view('elements3');
	}

	function add_fine()
	{
		$name = $_POST['fine_name'];
		$price = $_POST['fine_price'];

		$fine_model = new Model_Elements();
		$fine_model->type = FINE;
		$fine_model->name = $name;
		$fine_model->price = $price;
		$fine_model->save();

		$this->redirectToAction('elements');
	}

	function edit_fine()
	{
		$id = $_POST['fine_id'];
		$name = $_POST['fine_name'];
		$price = $_POST['fine_price'];

		$select = array('where' => 'id = '.$id);

		$fine_model = new Model_Elements($select);
		$fine_model->fetchOne();
		$fine_model->name = $name;
		$fine_model->price = $price;
		$fine_model->update();

		$this->redirectToAction('elements');
	}

	function delete_fine()
	{
		$id = $_POST['fine_id'];

		$fine_model = new Model_Elements();
		$select = array('where' => 'id = '.$id);
		$fine_model->deleteBySelect($select);

		$this->redirectToAction('elements');
	}

	function add_cost()
	{
		$name = $_POST['cost_name'];
		$price = $_POST['cost_price'];

		$cost_model = new Model_Elements();
		$cost_model->type = COST;
		$cost_model->name = $name;
		$cost_model->price = $price;
		$cost_model->save();

		$this->redirectToAction('elements');
	}

	function edit_cost()
	{
		$id = $_POST['cost_id'];
		$name = $_POST['cost_name'];
		$price = $_POST['cost_price'];

		$select = array('where' => 'id = '.$id);

		$cost_model = new Model_Elements($select);
		$cost_model->fetchOne();
		$cost_model->name = $name;
		$cost_model->price = $price;
		$cost_model->update();

		$this->redirectToAction('elements');
	}

	function delete_cost()
	{
		$id = $_POST['cost_id'];

		$cost_model = new Model_Elements();
		$select = array('where' => 'id = '.$id);
		$cost_model->deleteBySelect($select);

		$this->redirectToAction('elements');
	}

	function add_cost_team()
	{
		$element_id = $_POST['team_element'];
		$team_id = $_POST['team_cost_id'];

		$operation_model = new Model_Operations();
		$operation_model->element_id = $element_id;
		$operation_model->team_id = $team_id;

		$element_model = new Model_Elements();
		$element = $element_model->getRowById($element_id);

		$select = array('where' => 'id = '.$team_id);
		$team_model = new Model_Teams($select);
		$team_model->fetchOne();

		$team_model->score -= $element['price'];
		$team_model->update();

		$operation_model->name = $element['name'];
		$operation_model->type = $element['type'];
		$operation_model->state = $element['state'];
		$operation_model->price = $element['price'];
		$operation_model->residue = $team_model->score;
		$operation_model->save();


		$location = $_POST['location'];
		if ($location == 'index')
			$this->redirectToAction('index');
		if ($location == 'team')
			$this->redirectToAction('team/'.$team_id);
	}

	function add_order()
	{
		$name = $_POST['order_name'];
		$price = $_POST['order_price'];
		$comment = $_POST['order_comment'];

		$element_model = new Model_Elements();
		$element_model->name = $name;
		$element_model->price = $price;
                $element_model->old_price = $price;
		$element_model->type = ORDER;
		$element_model->state = ORDER_NOCONTROL;
		$element_model->comment = $comment;
		$element_model->save();

		$this->redirectToAction('elements2');
	}

	function edit_order()
	{
		$id = $_POST['order_id'];
		$name = $_POST['order_name'];
		$price = $_POST['order_price'];
		$comment = $_POST['order_comment'];

		$select = array('where' => 'id = '.$id);

		$element_model = new Model_Elements($select);
		$element_model->fetchOne();
		$element_model->name = $name;
		$element_model->price = $price;
                $element_model->old_price = $price;
		$element_model->comment = $comment;
		$element_model->update();

		$this->redirectToAction('elements2');
	}

	function delete_order()
	{
		$id = $_POST['order_id'];

		$element_model = new Model_Elements();
		$select = array('where' => 'id = '.$id);
		$element_model->deleteBySelect($select);

		$this->redirectToAction('elements2');
	}

	function add_part()
	{
		$name = $_POST['part_name'];
		$price = $_POST['part_price'];

		$element_model = new Model_Elements();
		$element_model->name = $name;
		$element_model->price = $price;
		$element_model->type = PART;
		$element_model->state = PART_NOBUY;
		$element_model->save();

		$this->redirectToAction('elements2');
	}

	function edit_part()
	{
		$id = $_POST['part_id'];
		$name = $_POST['part_name'];
		$price = $_POST['part_price'];

		$select = array('where' => 'id = '.$id);

		$element_model = new Model_Elements($select);
		$element_model->fetchOne();
		$element_model->name = $name;
		$element_model->price = $price;
		$element_model->update();

		$this->redirectToAction('elements2');
	}

	function delete_part()
	{
		$id = $_POST['part_id'];

		$element_model = new Model_Elements();
		$select = array('where' => 'id = '.$id);
		$element_model->deleteBySelect($select);

		$this->redirectToAction('elements2');
	}

	function clear()
	{
		$model = new Model_Users();
		$model->truncateTable();
		$model = new Model_Operations();
		$model->truncateTable();
		$model = new Model_Elements();
		$model->truncateTable();
		$model = new Model_Teams();
		$model->truncateTable();
		$model = new Model_Customers();
		$model->truncateTable();
		$model = new Model_Providers();
		$model->truncateTable();
		$model = new Model_Teams();
		$model->truncateTable();

		$model = new Model_Staffs();
		$model->truncateTable();

		$this->redirectToAction('index');
	}

	function stat()
	{
		$this->template->view('stat');
	}

	function get_stat_data()
	{
		$team_model = new Model_Teams();
		$teams = $team_model->getAllRows();

		foreach ($teams as $key => $team)
		{
			$select = array('where' => 'team_id = '.$team['id']);
			$operation_model = new Model_Operations($select);
			$operations = $operation_model->getAllRows();

			$teams[$key]['operations'] = $operations;
		}

		echo json_encode($teams);
	}

	function start()
	{
		$period_id = (isset($_GET['id'])) ? (int)$_GET['id'] : false;

		$team_model = new Model_Teams();
		$teams = $team_model->getAllRows();
		foreach ($teams as $key => $team)
		{
			$select = array('where' => 'id = '.$team['id']);
			$t = new Model_Teams($select);
			$t->fetchOne();
			$t->credit += $t->credit * 0.1;
			$t->update();
		}

		$select = array('where' => 'id = '.$period_id);
		$period_model = new Model_Periods($select);
		$period_model->fetchOne();
		$period_model->state = PERIOD_ENABLE;

		$period_model->update();

		$this->redirectToLink(REFERER);
	}

	function pause_period($args)
	{
		$period_id = $args[0];

		$select = array('where' => 'id = '.$period_id);
		$period_model = new Model_Periods($select);
		$period_model->fetchOne();
		$period_model->state = PERIOD_PAUSE;

		$period_model->update();

		$this->redirectToLink(REFERER);
	}

	function continue_period($args)
	{
		$period_id = $args[0];	

		$select = array('where' => 'id = '.$period_id);
		$period_model = new Model_Periods($select);
		$period_model->fetchOne();
		$period_model->state = PERIOD_ENABLE;

		$period_model->update();

		$this->redirectToLink(REFERER);
	}

	function complete_period($args)
	{
		$period_id = $args[0];	

		$select = array('where' => 'id = '.$period_id);
		$period_model = new Model_Periods($select);
		$period_model->fetchOne();
		$period_model->state = PERIOD_COMPLETED;

		$period_model->update();

		if ($period_id == PERIOD4)
		{
			$team_model = new Model_Teams();
			$teams = $team_model->getAllRows();
			foreach ($teams as $key => $team)
			{
				if ($team['credit'] > 0)
				{
					$select = array('where' => 'id = '.$team['id']);
					$t = new Model_Teams($select);
					$t->fetchOne();

					$operation_model = new Model_Operations();
					$operation_model->team_id = $team['id'];
					$operation_model->name = 'Выплата кредита';
					$operation_model->type = REPAYMENT;
					$operation_model->price = $t->credit;

					$t->score -= $t->credit;

					$operation_model->residue = $t->score;
					$operation_model->save();

					$t->credit = 0;
					$t->update();
				}
			}
		}

		// действия при конце периода

		$select = array('where' => "id = 'fine_time'");
		$game_model = new Model_Game($select);
		$game = $game_model->getOneRow();

		$select = array('where' => 'type = '.ORDER. ' and state = '.ORDER_CONTROL);
		$element_model = new Model_Elements($select);
		$elements = $element_model->getAllRows();

		if (isset($elements) && !empty($elements))
		{
			foreach ($elements as $key => $element)
			{
				$select = array('where' => 'id = '.$element['id']);
				$element_model = new Model_Elements($select);
				$element_model->fetchOne();
				$element_model->state = ORDER_OVERDUE;
				$element_model->update();
			}
		}

		$team_model = new Model_Teams();
		$teams = $team_model->getAllRows();

		if (!empty($teams))
		{
			foreach ($teams as $key => $team)
			{
				$salary = get_salary($team['id']);

				$select = array('where' => 'id = '.$team['id']);
				$team_model = new Model_Teams($select);
				$team_model->fetchOne();
				$team_model->score -= $salary['price'];
				$team_model->update();

				$operation_model = new Model_Operations();
				$operation_model->team_id = $team['id'];
				$operation_model->price = $salary['price'];
				$operation_model->residue = $team_model->score;
				$operation_model->type = SALARY;
				$operation_model->period_id = current_period();
				$operation_model->name = $salary['name'];
				$operation_model->save();
                               
            $price = 195000;
            $select = array('where' => 'id = '.$team['id']);
				$team_model = new Model_Teams($select);
				$team_model->fetchOne();
				$team_model->score -= $price;
				$team_model->update();

				$operation_model = new Model_Operations();
				$operation_model->team_id = $team['id'];
				$operation_model->price = $price;
				$operation_model->residue = $team_model->score;
				$operation_model->type = SALARY;
				$operation_model->period_id = current_period();
				$operation_model->name = 'Зарплата руководящему составу';
				$operation_model->save();
			}
		}

		$this->redirectToLink(REFERER);
	}

	function reset_period()
	{

	}

	function clear_periods()
	{
		$ids = array(PERIOD1, PERIOD2, PERIOD3, PERIOD4);
		foreach ($ids as $key => $id)
		{
			$select = array('where' => 'id = '.$id);
			$period = new Model_Periods($select);
			$period->fetchOne();
			$period->start = 'NULL';
			$period->end = 'NULL';
			$period->pause = 'NULL';
			$period->state = PERIOD_DISABLE;
			$period->update();
		}

		$team_model = new Model_Teams();
		$teams = $team_model->getAllRows();
		foreach ($teams as $key => $team)
		{
			$select = array('where' => 'id = '.$team['id']);
			$team_model = new Model_Teams($select);
			$team_model->fetchOne();									$select = array('where' => "id = 'default_score'");			$game_model = new Model_Game($select);			$game = $game_model->getOneRow();			
			$team_model->score = $game['value'];
			$team_model->credit = 'NULL';
			$team_model->update();
		}

		$model = new Model_Operations();
		$model->deleteBySelect();

		$this->redirectToLink(REFERER);
	}

	function settings()
	{
		$fine_time = $_POST['fine_time'];
		$credit_rate = $_POST['credit_rate'];

		$salary_trainee = $_POST['salary_trainee'];
		$salary_master = $_POST['salary_master'];
		$salary_prof = $_POST['salary_prof'];

                $select = array('where' => "id = 'fine_time'");
		$game_model = new Model_Game($select);
		$game_model->fetchOne();
		$game_model->value = $fine_time;
		$game_model->update();

		$select = array('where' => "id = 'credit_rate'");
		$game_model = new Model_Game($select);
		$game_model->fetchOne();
		$game_model->value = $credit_rate;
		$game_model->update();

                if(isset($_POST['default_score']))
                {
                     $default_score = $_POST['default_score'];
		     $select = array('where' => "id = 'default_score'");
		     $game_model = new Model_Game($select);
		     $game_model->fetchOne();
		     $game_model->value = $default_score;
		     $game_model->update();
                }

		$select = array('where' => "id = ".SKILL1);
		$skill_model = new Model_Skills($select);
		$skill_model->fetchOne();
		$skill_model->price = $salary_trainee;
		$skill_model->update();

		$select = array('where' => "id = ".SKILL2);
		$skill_model = new Model_Skills($select);
		$skill_model->fetchOne();
		$skill_model->price = $salary_master;
		$skill_model->update();

		$select = array('where' => "id = ".SKILL3);
		$skill_model = new Model_Skills($select);
		$skill_model->fetchOne();
		$skill_model->price = $salary_prof;
		$skill_model->update();

		$this->redirectToAction('index');
	}

	function add_cust_fine()
	{
		$name = $_POST['cust_fine_name'];
		$price = $_POST['cust_fine_price'];

		$fine_model = new Model_Elements();
		$fine_model->type = CUST_FINE;
		$fine_model->name = $name;
		$fine_model->price = $price;
		$fine_model->save();

		$this->redirectToAction('elements3');
	}

	function edit_cust_fine()
	{
		$id = $_POST['cust_fine_id'];
		$name = $_POST['cust_fine_name'];
		$price = $_POST['cust_fine_price'];

		$select = array('where' => 'id = '.$id);

		$fine_model = new Model_Elements($select);
		$fine_model->fetchOne();
		$fine_model->name = $name;
		$fine_model->price = $price;
		$fine_model->update();

		$this->redirectToAction('elements3');
	}

	function delete_cust_fine()
	{
		$id = $_POST['cust_fine_id'];

		$fine_model = new Model_Elements();
		$select = array('where' => 'id = '.$id);
		$fine_model->deleteBySelect($select);

		$this->redirectToAction('elements3');
	}

	function add_prom()
	{
		$name = $_POST['prom_name'];
		$price = $_POST['prom_price'];

		$cost_model = new Model_Elements();
		$cost_model->type = PROM;
		$cost_model->name = $name;
		$cost_model->price = $price;
		$cost_model->save();

		$this->redirectToAction('elements3');
	}

	function edit_prom()
	{
		$id = $_POST['prom_id'];
		$name = $_POST['prom_name'];
		$price = $_POST['prom_price'];

		$select = array('where' => 'id = '.$id);

		$cost_model = new Model_Elements($select);
		$cost_model->fetchOne();
		$cost_model->name = $name;
		$cost_model->price = $price;
		$cost_model->update();

		$this->redirectToAction('elements3');
	}

	function delete_prom()
	{
		$id = $_POST['prom_id'];

		$cost_model = new Model_Elements();
		$select = array('where' => 'id = '.$id);
		$cost_model->deleteBySelect($select);

		$this->redirectToAction('elements3');
	}

	function staffs($args)
	{
		$id = $args[0];

		if (!is_null($id))
		{

			$team_model = new Model_Teams();
			$team = $team_model->getRowById($id);

			$skill_model = new Model_Skills();
			$skills = $skill_model->getAllRows();

			$select = array('where' => 'team_id = '.$id);
			$staff_model = new Model_Staffs($select);
			$staffs = $staff_model->getAllRows();

			$team['salary'] = get_salary($id);

			$this->template->vars('team', $team);
			$this->template->vars('skills', $skills);
			$this->template->vars('staffs', $staffs);
			$this->template->view('staffs');
		}
		else
			$this->redirectToAction('index');
	}

	function save_staffs()
	{
		foreach ($_POST as $team_id => $value)
		{
			$select = array('where' => 'id = '.$team_id);
			$staff_model = new Model_Staffs($select);
			$staff_model->fetchOne();
			$staff_model->skill_id = $value;
			$staff_model->update();
		}

		$this->redirectToLink(REFERER);
	}

	function delete_staff()
	{
		$staff_id = $_POST['staff_id'];
		
		$select = array('where' => 'id = '.$staff_id);
		$staff_model = new Model_Staffs();
		$staff_model->deleteBySelect($select);

		echo 1;
	}

	function add_staff()
	{
		$staff_name = $_POST['staff_name'];
		$team_id = $_POST['team_id'];

		$staff_model = new Model_Staffs();
		$staff_model->name = $staff_name;
		$staff_model->team_id = $team_id;
		$staff_model->skill_id = SKILL1;
		$staff_model->save();

		$this->redirectToLink(REFERER);
	}

	function add_customer()
	{
		$customer_model = new Model_Customers();
		$customer_model->save();

		$customer_model = new Model_Customers();
		$customer = $customer_model->getLastRow();

		$select = array('where' => 'id = '.$customer['id']);
		$customer_model = new Model_Customers($select);
		$customer_model->fetchOne();
		$customer_model->login = 'customer'.$customer_model->id;
		$crypt_pass = generate_password(10);
		$customer_model->pass = $crypt_pass;
		$customer_model->update();

		$this->redirectToAction('customers');
	}

	function add_provider()
	{
		$provider_model = new Model_Providers();
		$provider_model->save();

		$provider_model = new Model_Providers();
		$provider = $provider_model->getLastRow();

		$select = array('where' => 'id = '.$provider['id']);
		$provider_model = new Model_Providers($select);
		$provider_model->fetchOne();
		$provider_model->login = 'provider'.$provider_model->id;
		$crypt_pass = generate_password(10);
		$provider_model->pass = $crypt_pass;
		$provider_model->update();

		$this->redirectToAction('providers');
	}

	function customers()
	{
		$customer_model = new Model_Customers();
		$customers = $customer_model->getAllRows();

		$this->template->vars('customers', $customers);
		$this->template->view('customers');
	}

	function providers()
	{
		$provider_model = new Model_Providers();
		$providers = $provider_model->getAllRows();

		$this->template->vars('providers', $providers);
		$this->template->view('providers');
	}

	function delete_customer()
	{
		$customer_id = $_POST['customer_id'];

		$select = array('where' => 'id = '.$customer_id);
		$customer_model = new Model_Customers();
		$customer_model->deleteBySelect($select);

		$this->redirectToAction('customers');
	}

	function delete_provider()
	{
		$provider_id = $_POST['provider_id'];

		$select = array('where' => 'id = '.$provider_id);
		$provider_model = new Model_Providers();
		$provider_model->deleteBySelect($select);

		$this->redirectToAction('providers');
	}

	function upload_orders()
	{
		if (file_exists($_FILES["filename"]["name"]))
			unlink($_FILES["filename"]["name"]);

		if(is_uploaded_file($_FILES["filename"]["tmp_name"]))
	     move_uploaded_file($_FILES["filename"]["tmp_name"], $_FILES["filename"]["name"]);

		if ($_FILES['filename']['name'] != "")
		{
			$filename = $_FILES['filename']['name'];
			$str = file_get_contents($filename);
			$lines = explode("\n", $str);
			for ($i = 0; $i < count($lines); $i++)
			{
				$line = explode(";", $lines[$i]);
				if (count($line) == 11 && $i > 0)
				{
					$name = $line[0];
					$price = $line[5];
					$desc = "Тип детали: ".$line[1].";\n";
					$desc .= "Упаковка: ".$line[3].";\n";
					$desc .= "Кол-во деталей: ".$line[6].";\n";
					$desc .= "S: ".$line[8].";\n";
					$desc .= "M: ".$line[9].";\n";
					$desc .= "L: ".$line[10];
					
					$element_model = new Model_Elements();
					$element_model->name = $name;
					$element_model->price = $price;
					$element_model->old_price = $price;
					$element_model->type = ORDER;
					$element_model->state = ORDER_NOCONTROL;
					$element_model->comment = $desc;
					$element_model->save();
				}
			}

			$this->redirectToAction('elements2');
		}
	}

	function delete_all_orders()
	{
		$select = array('where' => 'type = '.ORDER);
		$element_model = new Model_Elements();
		$element_model->deleteBySelect($select);

		$this->redirectToAction('elements2');
	}

	function table_teams()
	{
		$team_model = new Model_Teams();
		$teams = $team_model->getAllRows();

		foreach ($teams as $key => $team)
		{
			$select = array('where' => 'team_id = '.$team['id']);
			$user_model = new Model_Users($select);
			$user = $user_model->getOneRow();

			$teams[$key]['login'] = $user['login'];
			$teams[$key]['pass'] = $user['pass'];
		}

		$this->template->layouts = 'order_layouts';

		$this->template->vars('teams', $teams);
		$this->template->view('table_teams');
	}

	function table_customers()
	{
		$customer_model = new Model_Customers();
		$customers = $customer_model->getAllRows();

		$this->template->layouts = 'order_layouts';
		$this->template->vars('customers', $customers);

		$this->template->view('table_customers');
	}

	function table_providers()
	{
		$provider_model = new Model_Providers();
		$providers = $provider_model->getAllRows();

		$this->template->layouts = 'order_layouts';
		$this->template->vars('providers', $providers);
		
		$this->template->view('table_providers');
	}

	function table_parts()
	{
		$select = array('where' => 'type = '.PART);
		$element_model = new Model_Elements($select);
		$parts = $element_model->getAllRows();

		$this->template->layouts = 'order_layouts';
		$this->template->vars('parts', $parts);
		
		$this->template->view('table_parts');
	}
}