<?php
function dynamicradio_dradminheader()
{
	global $ins_admin_header_extra_modules_header;
	
	$link = '<span class="section-name"><a href="index.php?app=dynamicradio&module=main&section=overview">DynamicRadio</a></span>';
	
	$ins_admin_header_extra_modules_header .= $link;
}

function dynamicradio_dreventsindex()
{
	global $db, $events, $templates, $ins;
	
	$rows = $db->fetchAll('*', 'drevents', '1=1 ORDER BY `eid`', '0,2');
	
	if(empty($rows))
	{
		$eventlist = '<div class="bl1page-col">No event in the coming dates.</div>';
	}
	else
	{
		$events = 0;
		foreach($rows as $row)
		{
			if($row['date'] < date('Y-m-d'))
			{
				continue;
			}
			else
			{
				$events++;
			}
			$parts = explode('-', $row['date']);
			$dateTimeObj = DateTime::createFromFormat('!m', $parts[1]);
			$day = $dateTimeObj->format('F');
			
			eval("\$eventlist .= \"".$templates->get("eventslist")."\";"); 	
		}
		
		if($events == 0)
		{
			$eventlist = '<div class="bl1page-col">No event in the coming dates.</div>';
		}
	}	
	eval("\$events = \"".$templates->get("events")."\";"); 
}

function dynamicradio_dreventsindexsidebar()
{
	global $db, $eventlistsidebar, $templates, $ins;
	
	$rows = $db->fetchAll('*', 'drevents', '1=1 ORDER BY `eid`', '0,3');
	
	if(empty($rows))
	{
		$eventlist = '<div class="bl1page-col">No event in the coming dates.</div>';
	}
	else
	{
		$events = 0;
		foreach($rows as $row)
		{
			if($row['date'] < date('Y-m-d'))
			{
				continue;
			}
			else
			{
				$events++;
			}
			
			$shortname = $row['name'];
			if(strlen($row['name']) > 65)
			{
				$shortname = substr(0, 65, $shortname);
			}
			$parts = explode('-', $row['date']);
			$dateTimeObj = DateTime::createFromFormat('!m', $parts[1]);
			
			$month= $dateTimeObj->format('F');
			$year = $parts[0];
			$day = $parts[2];
			
			eval("\$eventlistsidebar .= \"".$templates->get("eventslistsidebar")."\";"); 	
		}
		
		if($events == 0)
		{
			$eventlistsidebar = '<div class="bl1page-col">No event in the coming dates.</div>';
		}
	}	
}
?>