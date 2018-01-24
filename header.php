<?

switch ($_SERVER['ORIG_PATH_INFO'])
{
	case "/index.php":
		$title = ' ';
		break;
	case "/conditions.php":
		$title = 'Правила проката - ';
		break;
	case "/sale.php":
		$title = 'Продажа нового снаряжения - ';
		break;
	case "/sale_bu.php":
		$title = 'Продажа б у снаряжение для туризма- палатки б у, рюкзак б у -  коммисионный магазин -   ';
		break;
	case "/sale_ruksak.php":
		$title = 'Продажа рюкзаков - ';
		break;
	case "/sale_palatka.php":
		$title = 'Продажа палаток - ';
		break;
	case "/sale_bicycle.php":
		$title = 'Продажа велосипедов - ';
		break;
	case "/address.php":
		$title = 'Контакты - ';
		break;
	case "/knigi.php":
		$title = 'Туризм книга - ';
		break;
	case "/prokat-region.php":
		$title = 'Прокат в регионах - ';
		break;
	case "/velomarsh.php":
		$title = 'Веломаршруты - ';
		break;
	case "/links.php":
		$title = 'Обмен ссылками  - ';
		break;
	case "/razmer-craft.php":
		$title = 'Подбор размера термобелья CRAFT- ';
		break;


}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<HTML>
<HEAD>
<TITLE><?echo $title ?>Прокат и продажа: лыж, палатки, рюкзаков, спальников, ковриков, байдарки, катамарана, прокат велосипедов
 - Киев.  Буча, Ирпень. -новое и  б у палатки, термобелье, лыжи, сноуборды, ботинки, buff.  Прокат палаток в  Киеве, Продажа палаток.
Прокат спальников. Прокат рюкзаков. Прокат ковриков. Прокат велосипедов  и другое Киев. В  Киеве, Буча, Ирпень.

</TITLE>
<meta name="verify-v1" content="BtU+IEju3rVtLVFjWnZUc53lYXpzZA+X3Ex+9P/7EJA=" ></meta>
<meta name="description" content="<?echo $title ?>Скидка на прокат 10%. При покупке снаряжения подарки. Новое и б у. Прокат  и продажа туристического снаряжения, корпоратив. Продажа и прокат палаток, прокат велосипедов, рюкзаков, спальников, ковриков, снегоступов, катамараны, байдарки, лодки, лыжи, сноуборды. Киев,  в Киеве, Ирпень, Буча, Бородянка, Ворзель, Стеколка, Немешаево"></meta>
<meta name="keywords" contents="<?echo $title ?>Прокат  и продажа туристичного спорядження на  корпоратив. Прокат туристического снаряжения, палатки, аренда снаряжения, прокат велосипедов, Киев,  в Киеве, Ирпень, Буча, Бородянка, Ворзель, Стеколка, Немешаево,  Ирпенский регион. прокат палаток, наметів, наплічників, спальников, рюкзаков, карематов, котлов, байдарок.Продажа снегоступов. Б/у туристическое снаряжение. Аренда палаток. Аренда рюкзаков. Аренда спальных мешков."></meta>
 <meta http-equiv="keywords" content="<?echo $title ?>Прокат  и продажа туристическое снаряжение на  корпоратив, доска объявлений,палатки. частные объявления о покупке продаже туристического, лыжного, горного снаряжения, бесплатные объявления, аренда туристического снаряжения, палатки, рюкзак, лыжи, сноуборды, спальник, GPS, продам куплю"> </meta>
<meta name="page-topic" content="<?echo $title ?>Прокат  и продажа туристическое снаряжение на  корпоратив, доска объявлений, палатки, частные объявления о покупке продаже туристического, лыжного, горного снаряжения, бесплатные объявления, аренда туристического снаряжения, палатки, рюкзак, лыжи, сноуборды, спальник, GPS, продам куплю"></meta>
 <meta name="page-type" content="<?echo $title ?>Прокат  и продажа туристическое снаряжение на  корпоратив, доска объявлений, палатки, частные объявления о покупке продаже туристического, лыжного, горного снаряжения, бесплатные объявления, аренда туристического снаряжения, палатки, рюкзак, лыжи, сноуборды, спальник, GPS, продам куплю"></meta>
 <meta name="audience" content="<?echo $title ?>Прокат  и продажа туристическое снаряжение на  корпоратив, доска объявлений, палатки, частные объявления о покупке продаже туристического, лыжного, горного снаряжения, бесплатные объявления, аренда туристического снаряжения, палатки, рюкзак, лыжи, сноуборды, спальник, GPS, продам куплю"></meta>
<meta name ="copyright" content="www.prokat.ho.com.ua"></meta>
<meta name="revisit-after" content="1 days"></meta>
<link rel="alternate" type="application/rss+xml" title="RSS" href="http://prokat.ho.com.ua/rssconfig.php" ></link>
<link href="titan.css" rel="stylesheet" type="text/css" charset="windows-1251"></link>
<script type="text/javascript" src="highslide/highslide.packed.js"></script>
<script src="jquery.js" type="text/javascript"></script>
<script src="main.js" type="text/javascript"></script>

<script type="text/javascript">

 hs.registerOverlay(
{
 thumbnailId: null,
 overlayId: 'controlbar',
 position: 'top right',
 hideOnMouseOut: true
} );

hs.graphicsDir = 'highslide/graphics/';
hs.showCredits = false;
hs.outlineType = 'rounded-white';
hs.captionEval = 'this.thumb.title';

</script>

<STYLE type="text/html">
body {font-family:Trebuchet MS, Arial;
	  color:black;
	  }
</STYLE>
</HEAD>
<BODY   link="Black" >



<table width="100%"  style="height: 100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-left: 30px;" width="0px" valign="top">
        	<h3>Экипировочный центр</h3><h2> "Мандрівник"<br /></h2>

        </td>
        <td align="left">
        		<table width="100%" cellpadding="0" cellspacing="0" border="0">
        			<tr>
        				<td>
        					<h1 style="font-size: 12px; padding-top: 3px; padding-left: 5%; margin-top: 0px; padding-bottom: 0px;">
								<?php

									switch ($_SERVER['ORIG_PATH_INFO'])
									{
										case "/index.php":
											echo 'ПРОКАТ ЛЫЖ, ПАЛАТОК, ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ КИЕВ, В КИЕВЕ, ИРПЕНЬ, БУЧА &nbsp;';
											break;
										case "/conditions.php":
											echo  'Правила проката - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ  &nbsp;';
											break;
										case "/sale.php":
											echo 'Продажа нового снаряжения - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp;';
											break;
										case "/sale_bu.php":
											echo 'Продажа бу снаряжения -  ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp;';
											break;
										case "/sale_ruksak.php":
											echo 'Продажа рюкзаков - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp;';
											break;
										case "/sale_palatka.php":
											echo 'Продажа палаток - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp;';
											break;
										case "/sale_bicycle.php":
											echo 'Продажа велосипедов - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp;';
											break;
										case "/address.php":
											echo 'Контакты - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp;';
											break;
										case "/knigi.php":
											echo 'Туризм книга - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp; ';
											break;
										case "/prokat-region.php":
											echo 'Прокат в регионах - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp; ';
											break;

										case "/velomarsh.php":
											echo 'Веломаршруты - ПРОКАТ И ПРОДАЖА ПАЛАТОК, СПАЛЬНИКОВ, РЮКЗАКОВ, КОВРИКОВ, ВЕЛОСИПЕДОВ &nbsp; ';
											break;
										case "/razmer-craft.php":
											echo 'Подбор размера термобелья CRAFT - термобелье в Украине';
											break;


									}

									?>


								<!--
								<img class="backlogo" src="/images/logomain.jpg">*/
								-->
								</h1>
        				</td>
        			</tr>
        			<td>
        					<img class="logo" alt="logo" src="/images/shapka1.jpg"/>
        			</td>
        		</table>

        </td>

    </tr>
</table>


