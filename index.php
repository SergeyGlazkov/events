<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/CIpWhois.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/CKudaGo.php');

$GLOBALS['events_in_radius'] = false;

$userIp = $_SERVER['REMOTE_ADDR'];
if(isset($_REQUEST['custom_ip']) && !empty($_REQUEST['custom_ip']))
	$userIp = $_REQUEST['custom_ip'];

$arEvents = [];
$title = "Город не определен";

$arLocation = CIpWhois::getLocationData($userIp);
if(!is_null($arLocation))
{
	$arEvents = CKudaGo::getEvents($arLocation);
	$title = "Ближайшие события в городе " . $arLocation['city'];
	if($GLOBALS['events_in_radius'])
		$title .= " в радиусе 200 км";
}
?> 

<html>
	<head>
		<meta charset="utf-8">
		<title><?=$title?></title>
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="/css/custom.css">
		<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
		<script src="/js/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-10">
					<h1><?=$title?></h1>
				</div>
				<div class="col-md-2">
					<form action="" method="GET">
						<label>IP-адрес:</label>
						<input placeholder="IP" name="custom_ip" value="<?=$userIp?>"/>
						<br>
						<input type="submit" value="Применить" />
					</form>
				</div>
			</div>
		</div>
		<div class="container events">
			<div class="row row-flex">
				<? if(empty($arEvents)): ?>
					<div class="col-md-12">
						События не найдены
					</div>
				<? else: ?>
					<? foreach($arEvents as $arEventItem): ?>
						<div class="col-md-3 col-sm-6 col-xs-12 event-item">
							<div class="event-card">
								<div class="image" style="background-image:url(<?=$arEventItem['image']?>);">
									<? if($arEventItem['age_restriction'] > 0): ?>
										<div class="age-restriction">
											<p><?=$arEventItem['age_restriction']?></p>
										</div>
									<? endif; ?>
								</div>
								<div class="title">
									<?=$arEventItem['title']?>
								</div>
								<div class="description">
									<?=$arEventItem['description']?>
								</div>
								<div class="footer-wrapper">
									<div class="row card-footer">
										<div class="col-xs-4 date date-one">
											<?=date("d.m.Y", $arEventItem['date'])?>
										</div>
										<div class="col-xs-8 link">
											<a href="<?=$arEventItem['site_url']?>" target="_blank">
												<button>Перейти</button>
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					<? endforeach; ?>
				<? endif; ?>
			</div>
		</div>
	</body>
</html>