var $ = jQuery;
		$(function(){
			$('#graph').graphify({
				//options: true,
				start: 'combo',
				obj: {
					id: 'ggg',
					width: '100%',
					height: 375,
					xGrid: false,
					legend: true,
					showPoints: false,
					title: '{$lang->admin_stats_numusers_graph}',
					points: [
						[7, 26, 33, 74, 12, 49, 33, 33, 74, 12, 49, 33]
					],
					//pointRadius: 3,
					colors: ['#1caf9a'],
					xDist: 100,
					dataNames: ['{$lang->admin_stats_numusers_graph}'],
					xName: 'Day',
					tooltipWidth: 15,
					animations: true,
					pointAnimation: true,
					//averagePointRadius: 5,
					design: {
						tooltipColor: '#fff',
						gridColor: '#f3f1f1',
						tooltipBoxColor: '#d9534f',
						averageLineColor: '#d9534f',
						pointColor: '#d9534f',
						lineStrokeColor: 'grey',
					}
				}
			});
		});