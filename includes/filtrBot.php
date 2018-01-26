<?php



//echo $_SERVER['HTTP_USER_AGENT'];
//if(stristr($_SERVER['HTTP_USER_AGENT'], 'go.mail.ru'))
$badQueryStrings = array(
							'?refresh',
							'fb_locale',
							'do=forum',
							'option=',
							'prdID',
							'password',
							'concat',
							'usertype',
							'select',
							'union',
							'query',
							'stripcslashes',
							'copy',
							'admin',
							'tp:'
);

for($i=0;$i<count($badQueryStrings);$i++)
{
	if(stristr($_SERVER['REQUEST_URI'], $badQueryStrings[$i]))
	{
		//header('Location: http://prokat.ho.com.ua');
		//echo $_SERVER['REQUEST_URI'];
		exit; //  - запрещаю вход для ненужных поисковых ботов и прочей нечести
	}
		
}
$badBots = array(
					//'yandex',
					'PycURL',
					'riddler',
					'ahrefs',
					'majestic',
					'msnbot',
					'bingbot',
					'grapeshot',
					'exabot',
					'meta',
					'obana',
					'help.zum.com',
					'ZumBot',
					'yahoo',
					'megaindex',
					'opensiteexplorer',
					'cliqz',
					'mj12bot',
					'zopilaryv',
					'mail.ru',
					'uptime'
);
for($i=0;$i<count($badBots);$i++)
{
	if(stristr($_SERVER['HTTP_USER_AGENT'], $badBots[$i]))
	{
		//header('Location: http://prokat.ho.com.ua');
		exit; //  - запрещаю вход для ненужных поисковых ботов и прочей нечести
	}
		
}

?>