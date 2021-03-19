<?php
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=rooms.csv");
header("Content-Transfer-Encoding: binary");
include_once('include/connect.php');
$sql="
	select
		mi.item_name
	from
		(
			select
				bma.management_area
			from
				building_management_areas bma
			where
				bma.management_area<>44
			and
				bma.building_id IN
				(
					select
						b.id
				from
					buildings b
				where
					web_post_area=2
				and
					b.deleted_flag=0
				)
			group by
				bma.management_area
		) tblA
		left join m_items mi
			on tblA.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
";
$result = mysqli_query($conn, $sql);
$arr_area=array();
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$arr_area[]=$row['item_name'];
}
//ç≈äÒÇËâwÇÃarrayçÏÇÈ
$sql="
	select
		tbl4.station_name
	from
		(
		select
			b2.id,
			b2.name
		from
			rooms r2
			left join plan_rooms pr2
				on r2.id=pr2.room_id
				and r2.deleted_flag=0
				and pr2.deleted_flag=0
			left join plans p2
				on pr2.plan_id=p2.id
				and p2.deleted_flag=0
				and p2.campaign_id IS NULL
			left join buildings b2
				on r2.building_id=b2.id
			left join prefectures pr
				on b2.prefecture_id=pr.id
		where
			r2.start_date <= CURDATE()
		and
			(r2.end_date >= CURDATE() or r2.end_date IS NULL)
		and
			p2.open_date <= CURDATE()
		and
			(p2.close_date >= CURDATE() or p2.close_date IS NULL)
		and
			b2.web_post_area=2
		and
			p2.web_post_kbn=1
		group by b2.id
		order by
			b2.name
		) openbdg
		left join (
		select
			tbl2.building_id,
			tbl3.station_name
		from
			(
			select
				tbl1.building_id,
				nbs2.station_group_code
			from
				(
				select
					nbs.building_id,
					MIN(nbs.distance) as nearest
				from
					near_by_stations nbs
				group by
					nbs.building_id
				) tbl1
				left join near_by_stations nbs2
					on tbl1.building_id=nbs2.building_id
					and tbl1.nearest=nbs2.distance
			) tbl2
			left join (
				select
					tblC.station_group_code,
					stat.station_name
				from
				(
					select
						tblA.station_group_code,
						tblB.sid
					from
					(
						select
							sta.station_group_code
						from
							stations sta
						where
							sta.opened_flag=1
						group by
							sta.station_group_code
					) tblA
					left join(
						select
							sta.station_group_code,
							MIN(sta.id) as sid
						from
							stations sta
						group by
							sta.station_group_code
					) tblB
					on tblA.station_group_code=tblB.station_group_code
				) tblC
				left join stations stat
				on tblC.sid=stat.id
			) tbl3
				on tbl2.station_group_code=tbl3.station_group_code
		) tbl4
			on openbdg.id=tbl4.building_id
	group by
		tbl4.station_name
";
$result = mysqli_query($conn, $sql);
$arr_station=array();
$tmp_station=array();
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$arr_station[]=$row['station_name'];
}

//èZèäÇÃarrayçÏÇÈ
$sql="
	SELECT
		cities.name
	FROM
		buildings
		INNER JOIN cities
			ON cities.id = buildings.city_id
			AND cities.deleted_flag = 0
		INNER JOIN plans
			ON plans.building_id = buildings.id
			AND (plans.open_date IS NULL OR plans.open_date <= CURDATE())
			AND (plans.close_date IS NULL OR plans.close_date >= CURDATE())
			AND plans.deleted_flag = 0
			AND plans.web_post_kbn = 1
		INNER JOIN (
			SELECT
				plans.id,
				MAX(
				CASE
					WHEN prices_17.account_type = '31' AND prices_17.term_unit_1 = '1' THEN prices_17.price1
					WHEN prices_17.account_type = '31' AND prices_17.term_unit_2 = '1' THEN prices_17.price2
					WHEN prices_17.account_type = '31' AND prices_17.term_unit_3 = '1' THEN prices_17.price3
					WHEN prices_16.account_type = '31' AND prices_16.term_unit_1 = '1' THEN prices_16.price1
					WHEN prices_16.account_type = '31' AND prices_16.term_unit_2 = '1' THEN prices_16.price2
					WHEN prices_16.account_type = '31' AND prices_16.term_unit_3 = '1' THEN prices_16.price3
					WHEN prices_1.account_type = '31' AND prices_1.term_unit_1 = '1' THEN prices_1.price1
					WHEN prices_1.account_type = '31' AND prices_1.term_unit_2 = '1' THEN prices_1.price2
					WHEN prices_1.account_type = '31' AND prices_1.term_unit_3 = '1' THEN prices_1.price3
				END
				) AS base_price_week
			FROM
				plans
				INNER JOIN plan_rooms
					ON plan_rooms.plan_id = plans.id
					AND plan_rooms.deleted_flag = 0
				INNER JOIN rooms
					ON rooms.id = plan_rooms.room_id
					AND rooms.start_date <= CURDATE()
					AND (rooms.end_date IS NULL OR rooms.end_date >= CURDATE())
					AND rooms.deleted_flag = 0
				LEFT JOIN plan_contract_types AS types_1
					ON types_1.plan_id = plans.id
					AND types_1.deleted_flag = 0
					AND types_1.contract_type_id = '1'
				LEFT JOIN plan_prices AS prices_1
					ON prices_1.plan_contract_type_id = types_1.id
					AND prices_1.deleted_flag = 0
					AND prices_1.account_type = 31
					AND (prices_1.term_unit_1 = '1' OR prices_1.term_unit_2 = '1' OR prices_1.term_unit_3 = '1')
				LEFT JOIN plan_contract_types AS types_16
					ON types_16.plan_id = plans.id
					AND types_16.deleted_flag = 0
					AND types_16.contract_type_id = '16'
				LEFT JOIN plan_prices AS prices_16
					ON prices_16.plan_contract_type_id = types_16.id
					AND prices_16.deleted_flag = 0
					AND prices_16.account_type = 31
					AND (prices_16.term_unit_1 = '1' OR prices_16.term_unit_2 = '1' OR prices_16.term_unit_3 = '1')
				LEFT JOIN plan_contract_types AS types_17
					ON types_17.plan_id = plans.id
					AND types_17.deleted_flag = 0
					AND types_17.contract_type_id = '17'
				LEFT JOIN plan_prices AS prices_17
					ON prices_17.plan_contract_type_id = types_17.id
					AND prices_17.deleted_flag = 0
					AND prices_17.account_type = 31
					AND (prices_17.term_unit_1 = '1' OR prices_17.term_unit_2 = '1' OR prices_17.term_unit_3 = '1')
			WHERE
				(plans.open_date IS NULL OR plans.open_date <= CURDATE())
			AND
				(plans.close_date IS NULL OR plans.close_date >= CURDATE())
			AND
				plans.web_post_kbn = 1
			AND
				plans.deleted_flag = 0
			GROUP BY
				plans.id
		) AS _room
			ON _room.id = plans.id
	WHERE
		buildings.web_post_area = '2'
	AND
		_room.base_price_week != 0
	AND
		buildings.deleted_flag = 0
	GROUP BY
		cities.name
";
$result = mysqli_query($conn, $sql);
$tmp_address=array();
$tmp_address2=array();
$arr_address=array();
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$tmp_address[]=$row['name'];
}

mb_convert_variables('SJIS','UTF-8',$tmp_address);
foreach($tmp_address as $value){
	$add_chk=mb_substr($value,-1,NULL,"SJIS");
	if($add_chk=="és"){
		$tmp_address2[]=$value;
	}else{
		if(mb_strpos($value,'és') !== false){
			$tmp_address2[]=str_replace(strstr($value,'és'),'',$value)."és";
		}else{
			$tmp_address2[]=$value;
		}
	}
	$val = array_unique($tmp_address2);
	$arr_address=array_values($val);
}
mb_convert_variables('UTF-8','SJIS',$arr_address);

//webéÂóvâwÇÃarrayçÏÇÈ
$arr_webstation=array();
$arr_webstation[0]="ìåãû";
$arr_webstation[1]="ïiêÏ";
$arr_webstation[2]="êVèh";
$arr_webstation[3]="èaíJ";
$arr_webstation[4]="írë‹";
$arr_webstation[5]="å‹îΩìc";
$arr_webstation[6]="çÇìcînèÍ";
$arr_webstation[7]="î—ìcã¥";
$arr_webstation[8]="èHótå¥";
$arr_webstation[9]="è„ñÏ";
$arr_webstation[10]="óºçë";
$arr_webstation[11]="ã—éÖí¨";
$arr_webstation[12]="êVè¨ä‚";
$arr_webstation[13]="ê¥êüîíâÕ";
$arr_webstation[14]="èüÇ«Ç´";
$arr_webstation[15]="ãÓçû";
$arr_webstation[16]="ìcí[";
$arr_webstation[17]="ê‘âH";
$arr_webstation[18]="ëÂã{";
$arr_webstation[19]="óßêÏ";
$arr_webstation[20]="ñ{î™î¶";
mb_convert_variables('UTF-8','SJIS',$arr_webstation);

//ç°ì˙àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= CURDATE()
	and
		(r.end_date >= DATE_ADD(CURDATE(), INTERVAL 1 MONTH) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(CURDATE(), INTERVAL 1 MONTH) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_end_date>=CURDATE()
			and
				cd.use_start_date<=CURDATE()
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		)
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN CURDATE() and DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
		)

	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
$stations=array();
$areas=array();
$address=array();
$webstations=array();
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][0])){
				$stations[$value][0]=$stations[$value][0]+1;
			}else{
				$stations[$value][0]=1;
			}
			break;
		}
	}

	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][0])){
				$areas[$value][0]=$areas[$value][0]+1;
			}else{
				$areas[$value][0]=1;
			}
			break;
		}
	}

	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][0])){
				$address[$value][0]=$address[$value][0]+1;
			}else{
				$address[$value][0]=1;
			}
			break;
		}
	}

}
//ç°ì˙àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id

		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code

	where
		r.start_date <= CURDATE()
	and
		(r.end_date >= DATE_ADD(CURDATE(), INTERVAL 1 MONTH) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(CURDATE(), INTERVAL 1 MONTH) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_end_date>=CURDATE()
			and
				cd.use_start_date<=CURDATE()
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN CURDATE() and DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ

	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][0])){
				$webstations[$value][0]=$webstations[$value][0]+1;
			}else{
				$webstations[$value][0]=1;
			}
			break;
		}
	}
}

//1èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 1 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 1 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 1 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 1 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][1])){
				$stations[$value][1]=$stations[$value][1]+1;
			}else{
				$stations[$value][1]=1;
			}
			break;
		}
	}
	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][1])){
				$areas[$value][1]=$areas[$value][1]+1;
			}else{
				$areas[$value][1]=1;
			}
			break;
		}
	}
	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][1])){
				$address[$value][1]=$address[$value][1]+1;
			}else{
				$address[$value][1]=1;
			}
			break;
		}
	}
}
//1èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id

		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code


	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 1 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 1 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 1 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 1 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][1])){
				$webstations[$value][1]=$webstations[$value][1]+1;
			}else{
				$webstations[$value][1]=1;
			}
			break;
		}
	}
}

//2èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 2 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 2 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 2 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 2 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 2 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][2])){
				$stations[$value][2]=$stations[$value][2]+1;
			}else{
				$stations[$value][2]=1;
			}
			break;
		}
	}
	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][2])){
				$areas[$value][2]=$areas[$value][2]+1;
			}else{
				$areas[$value][2]=1;
			}
			break;
		}
	}
	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][2])){
				$address[$value][2]=$address[$value][2]+1;
			}else{
				$address[$value][2]=1;
			}
			break;
		}
	}
}
//2èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id
		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code
	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 2 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 2 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 2 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 2 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 2 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][2])){
				$webstations[$value][2]=$webstations[$value][2]+1;
			}else{
				$webstations[$value][2]=1;
			}
			break;
		}
	}
}


// 3èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 3 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 3 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 3 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 3 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 3 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 3 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 3 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][3])){
				$stations[$value][3]=$stations[$value][3]+1;
			}else{
				$stations[$value][3]=1;
			}
			break;
		}
	}
	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][3])){
				$areas[$value][3]=$areas[$value][3]+1;
			}else{
				$areas[$value][3]=1;
			}
			break;
		}
	}
	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][3])){
				$address[$value][3]=$address[$value][3]+1;
			}else{
				$address[$value][3]=1;
			}
			break;
		}
	}
}
//3èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id

		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 3 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 3 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 3 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 3 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 3 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 3 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 3 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][3])){
				$webstations[$value][3]=$webstations[$value][3]+1;
			}else{
				$webstations[$value][3]=1;
			}
			break;
		}
	}
}


// 4èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 4 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 4 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 4 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 4 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 4 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 4 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 4 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][4])){
				$stations[$value][4]=$stations[$value][4]+1;
			}else{
				$stations[$value][4]=1;
			}
			break;
		}
	}
	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][4])){
				$areas[$value][4]=$areas[$value][4]+1;
			}else{
				$areas[$value][4]=1;
			}
			break;
		}
	}
	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][4])){
				$address[$value][4]=$address[$value][4]+1;
			}else{
				$address[$value][4]=1;
			}
			break;
		}
	}
}
//4èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id

		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 4 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 4 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 4 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 4 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 4 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 4 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 4 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][4])){
				$webstations[$value][4]=$webstations[$value][4]+1;
			}else{
				$webstations[$value][4]=1;
			}
			break;
		}
	}
}

// 5èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 5 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 5 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 5 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 5 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 5 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 5 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 5 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][5])){
				$stations[$value][5]=$stations[$value][5]+1;
			}else{
				$stations[$value][5]=1;
			}
			break;
		}
	}
	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][5])){
				$areas[$value][5]=$areas[$value][5]+1;
			}else{
				$areas[$value][5]=1;
			}
			break;
		}
	}
	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][5])){
				$address[$value][5]=$address[$value][5]+1;
			}else{
				$address[$value][5]=1;
			}
			break;
		}
	}
}
//5èTä‘å„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id

		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 5 WEEK)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 5 WEEK) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 5 WEEK) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 5 WEEK)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 5 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 5 WEEK) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 5 WEEK)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][5])){
				$webstations[$value][5]=$webstations[$value][5]+1;
			}else{
				$webstations[$value][5]=1;
			}
			break;
		}
	}
}

// 2Éïåéå„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóâÒÇ∑
$sql="
	select
		b.name,
		r.name,
		tbl_station.station_name,
		mi.item_name as area,
		ci.name as address
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		left join m_items mi
			on bma.management_area=mi.item_cd
			and mi.item_group_cd='management_areas'
		left join cities ci
			on b.city_id=ci.id

		-- ç≈äÒÇËâwåãçá
		left join (

			select
				tbl2.building_id,
				tbl3.station_name
			from
				(
				select
					tbl1.building_id,
					nbs2.station_group_code
				from
					(
					select
						nbs.building_id,
						MIN(nbs.distance) as nearest
					from
						near_by_stations nbs
					group by
						nbs.building_id
					) tbl1
					left join near_by_stations nbs2
						on tbl1.building_id=nbs2.building_id
						and tbl1.nearest=nbs2.distance
				) tbl2
				left join (
			select
				tblC.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
			on tblC.sid=stat.id
				) tbl3
					on tbl2.station_group_code=tbl3.station_group_code

		) tbl_station
		on b.id=tbl_station.building_id

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 MONTH) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 MONTH) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 2 MONTH) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 2 MONTH)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	//ç≈äÒÇËâwñàarrayäiî[
	foreach($arr_station as $key => $value){
		if($row['station_name']==$value){
			if(isset($stations[$value][6])){
				$stations[$value][6]=$stations[$value][6]+1;
			}else{
				$stations[$value][6]=1;
			}
			break;
		}
	}
	//âcã∆ÉGÉäÉAñà
	foreach($arr_area as $key => $value){
		if($row['area']==$value){
			if(isset($areas[$value][6])){
				$areas[$value][6]=$areas[$value][6]+1;
			}else{
				$areas[$value][6]=1;
			}
			break;
		}
	}
	//èZèäñà
	$add_chk=mb_substr($row['address'],-1);
	$add_chk=mb_convert_encoding($add_chk,"SJIS","UTF-8");
	if($add_chk=="és"){
		$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
	}else{
		if(mb_strpos(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és') !== false){
			$ad=str_replace(strstr(mb_convert_encoding($row['address'],"SJIS","UTF-8"),'és'),'',mb_convert_encoding($row['address'],"SJIS","UTF-8"))."és";
		}else{
			$ad=mb_convert_encoding($row['address'],"SJIS","UTF-8");
		}
	}
	$ad=mb_convert_encoding($ad,"UTF-8","SJIS");

	foreach($arr_address as $key => $value){
		if($ad==$value){
			if(isset($address[$value][6])){
				$address[$value][6]=$address[$value][6]+1;
			}else{
				$address[$value][6]=1;
			}
			break;
		}
	}
}
//2Éïåéå„Ç…àƒì‡â¬î\Ç»ãÛé∫àÍóóÇÃwebéÂóvâwâwé¸ï”
$sql="
	select
		b.name,
		r.name,
		tbl_stations.station_name
	from
		rooms r
		left join plan_rooms pr
			on r.id=pr.room_id
			and r.deleted_flag=0
			and pr.deleted_flag=0
		left join plans p
			on pr.plan_id=p.id
			and p.deleted_flag=0
			and p.campaign_id IS NULL
		left join buildings b
			on r.building_id=b.id
		left join building_management_areas bma
			on b.id=bma.building_id
			and bma.deleted_flag=0
		inner join near_by_stations nbs
			on b.id=nbs.building_id
		left join(
			select
				stat.station_group_code,
				stat.station_name
			from
			(
				select
					tblA.station_group_code,
					tblB.sid
				from
				(
					select
						sta.station_group_code
					from
						stations sta
					where
						sta.opened_flag=1
					group by
						sta.station_group_code
				) tblA
				left join(
					select
						sta.station_group_code,
						MIN(sta.id) as sid
					from
						stations sta
					group by
						sta.station_group_code
				) tblB
				on tblA.station_group_code=tblB.station_group_code
			) tblC
			left join stations stat
				on tblC.sid=stat.id

		) tbl_stations
			on nbs.station_group_code=tbl_stations.station_group_code

	where
		r.start_date <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
	and
		(r.end_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 MONTH) or r.end_date IS NULL)
	and
		p.open_date >= '2018-05-14'
	and
		(p.close_date >= DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 2 MONTH) or p.close_date IS NULL)
	and
		b.web_post_area=2
	and
		p.web_post_kbn=1
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
				left join rooms r on cd.room_id=r.id
			where
				cd.use_end_date>=DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
			and
				cd.use_start_date<=DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3
			group by
				cd.room_id
		) -- ì¸ãèíÜÇÃÇ‡ÇÃ
	and
		r.id NOT IN
		(
			select
				cd.room_id
			from
				contract_details cd
			where
				cd.use_start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 2 MONTH) and DATE_ADD(DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 1 DAY), INTERVAL 2 MONTH)
			and
				cd.deleted_flag=0
			and
				cd.disabled_flag=0
			and
				cd.contract_status <> 3

		) -- ì¸ãèó\íËÇÃÇ‡ÇÃ
	order by
		b.name,
		r.name
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	//webéÂóvâw
	foreach($arr_webstation as $key => $value){
		if($row['station_name']==$value){
			if(isset($webstations[$value][6])){
				$webstations[$value][6]=$webstations[$value][6]+1;
			}else{
				$webstations[$value][6]=1;
			}
			break;
		}
	}
}


// âÊñ èoóÕ
$sum=array();
$sum[0]=0;
$sum[1]=0;
$sum[2]=0;
$sum[3]=0;
$sum[4]=0;
$sum[5]=0;
$sum[6]=0;

$areasum=array();
$areasum[0]=0;
$areasum[1]=0;
$areasum[2]=0;
$areasum[3]=0;
$areasum[4]=0;
$areasum[5]=0;
$areasum[6]=0;

$addresssum=array();
$addresssum[0]=0;
$addresssum[1]=0;
$addresssum[2]=0;
$addresssum[3]=0;
$addresssum[4]=0;
$addresssum[5]=0;
$addresssum[6]=0;

$stationsum=array();
$stationsum[0]=0;
$stationsum[1]=0;
$stationsum[2]=0;
$stationsum[3]=0;
$stationsum[4]=0;
$stationsum[5]=0;
$stationsum[6]=0;

$title=array();
$title[0]="ç°ì˙";
$title[1]="1èTå„";
$title[2]="2èTå„";
$title[3]="3èTå„";
$title[4]="4èTå„";
$title[5]="5èTå„";
$title[6]="2Éïåéå„";

echo "âwñº,";
foreach($title as $key => $value){
	echo $value.",";
}
echo "\n";

foreach($arr_station as $key => $value){
	echo mb_convert_encoding($value,"SJIS", 'UTF-8').",";

	if(isset($stations[$value][0])){
		echo $stations[$value][0];
		$sum[0]=$sum[0]+$stations[$value][0];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($stations[$value][1])){
		echo $stations[$value][1];
		$sum[1]=$sum[1]+$stations[$value][1];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($stations[$value][2])){
		echo $stations[$value][2];
		$sum[2]=$sum[2]+$stations[$value][2];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($stations[$value][3])){
		echo $stations[$value][3];
		$sum[3]=$sum[3]+$stations[$value][3];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($stations[$value][4])){
		echo $stations[$value][4];
		$sum[4]=$sum[4]+$stations[$value][4];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($stations[$value][5])){
		echo $stations[$value][5];
		$sum[5]=$sum[5]+$stations[$value][5];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($stations[$value][6])){
		echo $stations[$value][6];
		$sum[6]=$sum[6]+$stations[$value][6];
	}else{
		echo "0";
	}
	echo ",";
	echo "\n";
}
echo "çáåv,".$sum[0].",".$sum[1].",".$sum[2].",".$sum[3].",".$sum[4].",".$sum[5].",".$sum[6];
echo "\n";
echo "\n";

echo "ÉGÉäÉAñº,";
foreach($title as $key => $value){
	echo $value.",";
}
echo "\n";

foreach($arr_area as $key => $value){
	echo mb_convert_encoding($value,"SJIS", "UTF-8").",";

	if(isset($areas[$value][0])){
		echo $areas[$value][0];
		$areasum[0]=$areasum[0]+$areas[$value][0];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($areas[$value][1])){
		echo $areas[$value][1];
		$areasum[1]=$areasum[1]+$areas[$value][1];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($areas[$value][2])){
		echo $areas[$value][2];
		$areasum[2]=$areasum[2]+$areas[$value][2];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($areas[$value][3])){
		echo $areas[$value][3];
		$areasum[3]=$areasum[3]+$areas[$value][3];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($areas[$value][4])){
		echo $areas[$value][4];
		$areasum[4]=$areasum[4]+$areas[$value][4];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($areas[$value][5])){
		echo $areas[$value][5];
		$areasum[5]=$areasum[5]+$areas[$value][5];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($areas[$value][6])){
		echo $areas[$value][6];
		$areasum[6]=$areasum[6]+$areas[$value][6];
	}else{
		echo "0";
	}
	echo ",";
	echo "\n";
}
echo "çáåv,".$areasum[0].",".$areasum[1].",".$areasum[2].",".$areasum[3].",".$areasum[4].",".$areasum[5].",".$areasum[6];
echo "\n";
echo "\n";

echo "èZèä,";
foreach($title as $key => $value){
	echo $value.",";
}
echo "\n";
foreach($arr_address as $key => $value){
	echo mb_convert_encoding($value,"SJIS", "UTF-8").",";
	if(isset($address[$value][0])){
		echo $address[$value][0];
		$addresssum[0]=$addresssum[0]+$address[$value][0];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($address[$value][1])){
		echo $address[$value][1];
		$addresssum[1]=$addresssum[1]+$address[$value][1];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($address[$value][2])){
		echo $address[$value][2];
		$addresssum[2]=$addresssum[2]+$address[$value][2];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($address[$value][3])){
		echo $address[$value][3];
		$addresssum[3]=$addresssum[3]+$address[$value][3];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($address[$value][4])){
		echo $address[$value][4];
		$addresssum[4]=$addresssum[4]+$address[$value][4];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($address[$value][5])){
		echo $address[$value][5];
		$addresssum[5]=$addresssum[5]+$address[$value][5];
	}else{
		echo "0";
	}
	echo ",";
	if(isset($address[$value][6])){
		echo $address[$value][6];
		$addresssum[6]=$addresssum[6]+$address[$value][6];
	}else{
		echo "0";
	}
	echo ",";
	echo "\n";
}
echo "çáåv,".$addresssum[0].",".$addresssum[1].",".$addresssum[2].",".$addresssum[3].",".$addresssum[4].",".$addresssum[5].",".$addresssum[6];
echo "\n";
echo "\n";

echo "webéÂóvâwñº,";
foreach($title as $key => $value){
	echo $value.",";
}
echo "\n";

$i=1;
foreach($arr_webstation as $key => $value){
//	echo $value.",";
	$valuechk=mb_convert_encoding($value,"SJIS","UTF-8");
	if($valuechk=="ìåãû" || $valuechk=="ïiêÏ" || $valuechk=="çÇìcînèÍ" || $valuechk=="è„ñÏ" || $valuechk=="óºçë" || $valuechk=="ã—éÖí¨" || $valuechk=="ãÓçû" || $valuechk=="ñ{î™î¶"){
		$i=2;
	}elseif($valuechk=="å‹îΩìc"||$valuechk=="èHótå¥"||$valuechk=="ëÂã{"){
		$i=3;
	}elseif($valuechk=="î—ìcã¥"){
		$i=4;
	}elseif($valuechk=="êVèh"||$valuechk=="èaíJ"||$valuechk=="írë‹"){
		$i=5;
	}else{
		$i=1;
	}
	echo mb_convert_encoding($value,"SJIS", 'UTF-8').",";

	if(isset($webstations[$value][0])){
		echo floor($webstations[$value][0]/$i);
		$stationsum[0]=$stationsum[0]+floor($webstations[$value][0]/$i);
	}else{
		echo "0";
	}
	echo ",";
	if(isset($webstations[$value][1])){
		echo floor($webstations[$value][1]/$i);
		$stationsum[1]=$stationsum[1]+floor($webstations[$value][1]/$i);
	}else{
		echo "0";
	}
	echo ",";
	if(isset($webstations[$value][2])){
		echo floor($webstations[$value][2]/$i);
		$stationsum[2]=$stationsum[2]+floor($webstations[$value][2]/$i);
	}else{
		echo "0";
	}
	echo ",";
	if(isset($webstations[$value][3])){
		echo floor($webstations[$value][3]/$i);
		$stationsum[3]=$stationsum[3]+floor($webstations[$value][3]/$i);
	}else{
		echo "0";
	}
	echo ",";
	if(isset($webstations[$value][4])){
		echo floor($webstations[$value][4]/$i);
		$stationsum[4]=$stationsum[4]+floor($webstations[$value][4]/$i);
	}else{
		echo "0";
	}
	echo ",";
	if(isset($webstations[$value][5])){
		echo floor($webstations[$value][5]/$i);
		$stationsum[5]=$stationsum[5]+floor($webstations[$value][5]/$i);
	}else{
		echo "0";
	}
	echo ",";
	if(isset($webstations[$value][6])){
		echo floor($webstations[$value][6]/$i);
		$stationsum[6]=$stationsum[6]+floor($webstations[$value][6]/$i);
	}else{
		echo "0";
	}
	echo ",";
	echo "\n";
}
echo "çáåv,".$stationsum[0].",".$stationsum[1].",".$stationsum[2].",".$stationsum[3].",".$stationsum[4].",".$stationsum[5].",".$stationsum[6];
echo "\n";
echo "\n";
?>