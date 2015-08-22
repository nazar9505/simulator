<?php include_once('_menu.php') ?>
	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading">
					Общие данные
				</div>
				<div class="table-responsive">
					
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>Команда</th>
								<th>Cчет</th>
								<th>Транзакция</th>
								<th>Описание</th>
								<th>Тип</th>
								<th>Добавить расход</th>
								<th>Кредиты</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($data['teams'])): ?>
								<?php foreach ($data['teams'] as $key => $team): ?>
									<tr>
										<td>
											<a href="/admin/team/<?php print $team['id'] ?>">
												<?php print $team['name'] ?>
											</a>
										</td>
										<td><?php print $team['score'] ?></td>
										<?php if (isset($team['operation']) && !empty($team['operation'])): ?>
											<td class="
															<?php if ($team['operation']['price'] != 0): ?>
																<?php print (($team['operation']['type'] != PROM && $team['operation']['type'] != CREDIT && $team['operation']['type'] != ORDER || $team['operation']['state'] == ORDER_OVERDUE) ? 'danger' : 'success')  ?>
															<?php endif ?>
														">
												<?php
													if ($team['operation']['price'] != 0)
													{
														print (($team['operation']['type'] != PROM && $team['operation']['type'] != CREDIT && $team['operation']['type'] != ORDER || $team['operation']['state'] == ORDER_OVERDUE) ? '-' : '+');
													}
												?>
												<?php print $team['operation']['price'] ?>
											</td>
										<?php else: ?>
											<td></td>
										<?php endif ?>
										<td>
											<?php if (isset($team['operation'])): ?>
												<?php print $team['operation']['name'] ?>
											<?php else: ?>
												-
											<?php endif ?>
										</td>
										<td>
											<?php if (isset($team['operation'])): ?>
												<?php
													if ($team['operation']['type'] == ORDER) {
														print 'Заказ ';
														if ($team['operation']['state'] == ORDER_CONTROL) {
															print '(на исполнении)';
														}elseif($team['operation']['state'] == ORDER_COMPLETED) {
															print '(выполнен)';
														}elseif($team['operation']['state'] == ORDER_OVERDUE) {
															print '(просрочен)';
														}
													}
													if ($team['operation']['type'] == COST) {
														print 'Расход';
													}
													if ($team['operation']['type'] == FINE) {
														print 'Штраф';
													}
													if ($team['operation']['type'] == PROM) {
														print 'Поощрение';
													}
													if ($team['operation']['type'] == CUST_FINE) {
														print 'Штраф заказчика';
													}
													if ($team['operation']['type'] == CREDIT) {
														print 'Кредит';
													}
													if ($team['operation']['type'] == PART) {
														print 'Деталь';
													}
												?>
											<?php endif ?>
										</td>
										<td class="text-center">
											<button class="btn-add-cost btn btn-default" data-id="<?php print $team['id'] ?>" data-toggle="modal" data-target="#addCostTeam">
												<span class="glyphicon glyphicon-plus"></span>
												Добавить расход
											</button>
										</td>
										<td>
											<?php print $team['credit_count'] ?>
										</td>
									</tr>
								<?php endforeach ?>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					Запрашиваемые кредиты
				</div>
				<div class="panel-body">
					<?php if (isset($data['credits']) && !empty($data['credits'])): ?>
						<div>
							Всего запрашиваемых кредитов: <?php print count($data['credits']) ?>
						</div>
						<div>
							<?php
								$now_credits = array_filter($data['credits'], function($a)
								{
									return $a['period_id'] == current_period();
								});
							?>
							Кредитов на текущий период: <?php print count($now_credits) ?>
						</div>
						<div>
							<?php 
								$next_credits = array_filter($data['credits'], function($a)
								{
									return $a['period_id'] == current_period()+1;
								});
							?>
							Кредитов на следующий период: <?php print count($next_credits) ?>
						</div>
					<?php else: ?>
						Нет запрашиваемых кредитов
					<?php endif ?>
				</div>
			</div>
			<a href="/admin/clear" class="btn btn-danger">Очистить все данные</a>
			<a href="/admin/clear_periods" class="btn btn-danger">Сбросить игру</a>
			<hr>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">Настройки</div>
						<div class="panel-body">
							<form action="/admin/settings" method="post">
								<div class="form-group">
									<label class="control-label" for="">Штраф за просрочку</label>	
									<div class="input-group">
										<div class="input-group-addon">%</div>
										<input class="form-control" name="fine_time" type="text" value="<?php print $data['fine_time']['value'] ?>">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label" for="">Время одного периода</label>	
									<div class="input-group">
										<div class="input-group-addon">мин.</div>
										<input class="form-control" name="period_time" type="text" value="<?php print $data['period_time']['value'] ?>">
									</div>
								</div>
								<button type="submit" class="btn btn-default">Сохранить</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="addCostTeam" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
     <form id="form-team-cost" action="/admin/add_cost_team" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">Добавить расход</span></h4>
      </div>
      <div class="modal-body">
			<div class="form-group">
				<label for="recipient-name" class="control-label">Название:</label>
				<select id="select-elements" class="form-control">
					<option value=""></option>
					<?php if (!empty($data['costs'])): ?>
						<option class="option-cost" value="cost">Расход</option>
					<?php endif ?>
					<?php if (!empty($data['fines'])): ?>
						<option class="option-fine" value="fine">Штраф</option>
					<?php endif ?>
				</select>
			</div>
			<div class="select-element select-cost form-group">
				<select id="" class="form-control">
					<?php foreach ($data['costs'] as $key => $cost): ?>
						<option value="<?php print $cost['id'] ?>"><?php print $cost['price'] ?> руб. | <?php print $cost['name'] ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="select-element select-fine form-group">
				<select id="" class="form-control">
					<?php foreach ($data['fines'] as $key => $fine): ?>
						<option value="<?php print $fine['id'] ?>"><?php print $fine['price'] ?> руб. | <?php print $fine['name'] ?></option>
					<?php endforeach ?>
				</select>
			</div>
      </div>
      <input name="team_element" type="hidden">
      <input name="team_cost_id" type="hidden">
      <input name="location" type="hidden" value="index">
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
        <button type="submit" class="select-btn btn btn-primary" disabled>Добавить</button>
      </div>
     </form>
    </div>
  </div>
</div>