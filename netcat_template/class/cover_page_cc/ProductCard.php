<?php
/**
 * Created by PhpStorm.
 * User: Seyritey
 * Date: 22.05.2018
 * Time: 17:48
 */

class ProductCard
{
    public $car_name;
    public $mark_name;
    public $regs = [
            'main' => [
                    'id' => 1,
                    'city_pred' => 'Набережные Челны',
                    'city_vin' => 'Набережных Челнах',
                    'city_imen' => 'Набережные Челны',
            ],
            'spb' => [
                    'id' => 7,
                    'city_pred' => 'Санкт-Петербург',
                    'city_vin' => 'Санкт-Петербурге',
                    'city_imen' => 'Санкт-Петербург',
            ],
            'msk' => [
                    'id' => 14,
                    'city_pred' => 'Москву',
                    'city_vin' => 'Москве',
                    'city_imen' => 'Москва',
            ],
            'kzn' => [
                    'id' => 5,
                    'city_pred' => 'Казань',
                    'city_vin' => 'Казани',
                    'city_imen' => 'Казань',
            ],
            'smr' => [
                    'id' => 8,
                    'city_pred' => 'Самару',
                    'city_vin' => 'Самаре',
                    'city_imen' => 'Самара',
            ],
            'ufa' => [
                    'id' => 11,
                    'city_pred' => 'Уфу',
                    'city_vin' => 'Уфе',
                    'city_imen' => 'Уфа',
            ],
            'tyumen' => [
                    'id' => 9,
                    'city_pred' => 'Тюмень',
                    'city_vin' => 'Тюмени',
                    'city_imen' => 'Тюмень',
            ],
            'irk' => [
                    'id' => 12,
                    'city_pred' => 'Иркутск',
                    'city_vin' => 'Иркутске',
                    'city_imen' => 'Иркутск',
            ],
            'ekb' => [
                    'id' => 13,
                    'city_pred' => 'Екатеринбург',
                    'city_vin' => 'Екатеринбурге',
                    'city_imen' => 'Екатеринбург',
            ],
            'barn' => [
                    'id' => 18,
                    'city_pred' => 'Барнаул',
                    'city_vin' => 'Барнауле',
                    'city_imen' => 'Барнаул',
            ],
            'ast' => [
                    'id' => 17,
                    'city_pred' => 'Астрахань',
                    'city_vin' => 'Астрахани',
                    'city_imen' => 'Астрахань',
            ],
            'perm' => [
                    'id' => 19,
                    'city_pred' => 'Пермь',
                    'city_vin' => 'Перми',
                    'city_imen' => 'Пермь',
            ],
            'tomsk' => [
                    'id' => 20,
                    'city_pred' => 'Томск',
                    'city_vin' => 'Томске',
                    'city_imen' => 'Томск',
            ],
            'yl' => [
                    'id' => 21,
                    'city_pred' => 'Ульяновск',
                    'city_vin' => 'Ульяновске',
                    'city_imen' => 'Ульяновск',
            ],
            'yar' => [
                    'id' => 22,
                    'city_pred' => 'Ярославль',
                    'city_vin' => 'Ярославле',
                    'city_imen' => 'Ярославль',
            ],
            'chelyabinsk' => [
                    'id' => 24,
                    'city_pred' => 'Челябинск',
                    'city_vin' => 'Челябинске',
                    'city_imen' => 'Челябинск',
            ],
            'tol' => [
                    'id' => 25,
                    'city_pred' => 'Тольятти',
                    'city_vin' => 'Тольятти',
                    'city_imen' => 'Тольятти',
            ],
            'rzn' => [
                    'id' => 26,
                    'city_pred' => 'Рязань',
                    'city_vin' => 'Рязани',
                    'city_imen' => 'Рязань',
            ],
            'saratov' => [
                    'id' => 27,
                    'city_pred' => 'Саратове',
                    'city_vin' => 'Саратове',
                    'city_imen' => 'Саратов',
            ],
            'rostov' => [
                    'id' => 28,
                    'city_pred' => 'Ростов',
                    'city_vin' => 'Ростове',
                    'city_imen' => 'Ростов',
            ],
            'volgograd' => [
                    'id' => 29,
                    'city_pred' => 'Волгоград',
                    'city_vin' => 'Волгограде',
                    'city_imen' => 'Волгоград',
            ],
            'voron' => [
                    'id' => 30,
                    'city_pred' => 'Воронеж',
                    'city_vin' => 'Воронеже',
                    'city_imen' => 'Воронеж',
            ],
            'izh' => [
                    'id' => 31,
                    'city_pred' => 'Ижевск',
                    'city_vin' => 'Ижевске',
                    'city_imen' => 'Ижевск',
            ],
            'kemerovo' => [
                    'id' => 32,
                    'city_pred' => 'Кемерово',
                    'city_vin' => 'Кемерово',
                    'city_imen' => 'Кемерово',
            ],
            'kirov' => [
                    'id' => 33,
                    'city_pred' => 'Киров',
                    'city_vin' => 'Кирове',
                    'city_imen' => 'Киров',
            ],
            'krasnodar' => [
                    'id' => 34,
                    'city_pred' => 'Краснодар',
                    'city_vin' => 'Краснодаре',
                    'city_imen' => 'Краснодар',
            ],
            'krasnoyarsk' => [
                    'id' => 35,
                    'city_pred' => 'Красноярск',
                    'city_vin' => 'Красноярске',
                    'city_imen' => 'Красноярск',
            ],
            'nnov' => [
                    'id' => 36,
                    'city_pred' => 'Нижнем Новгород',
                    'city_vin' => 'Нижнем Новгороде',
                    'city_imen' => 'Нижний Новгород',
            ],
            'novokz' => [
                    'id' => 37,
                    'city_pred' => 'Новокузнецк',
                    'city_vin' => 'Новокузнецке',
                    'city_imen' => 'Новокузнецк',
            ],
            'novosib' => [
                    'id' => 38,
                    'city_pred' => 'Новосибирск',
                    'city_vin' => 'Новосибирске',
                    'city_imen' => 'Новосибирск',
            ],
            'omsk' => [
                    'id' => 39,
                    'city_pred' => 'Омск',
                    'city_vin' => 'Омске',
                    'city_imen' => 'Омск',
            ],
            'penza' => [
                    'id' => 40,
                    'city_pred' => 'Пензу',
                    'city_vin' => 'Пензе',
                    'city_imen' => 'Пенза',
            ],
            'orenburg' => [
                    'id' => 41,
                    'city_pred' => 'Оренбург',
                    'city_vin' => 'Оренбурге',
                    'city_imen' => 'Оренбург',
            ],
    ];
    public $regionName;
    public $titleTemplate;
    public $descriptionTemplate;
    public $productDescTemplate;
    private $reviews;
    private $transliteratedWords;
    private $markName;
    private $modelName;

    /**
     * @param $reviews
     * @param $regionId
     * @param $titleTemplate
     * @param $descriptionTemplate
     * @param $productDescTemplate
     * @param $markName
     * @param $modelName
     */
    public function __construct(
            $reviews,
            $regionId,
            $titleTemplate,
            $descriptionTemplate,
            $productDescTemplate,
			$markName,
			$modelName
    ) {
        $this->reviews = $reviews;
        $this->reviews->checkAdd();
        $this->regionName = $this->getRegName($regionId);
        $this->titleTemplate = $titleTemplate;
        $this->descriptionTemplate = $descriptionTemplate;
        $this->productDescTemplate = $productDescTemplate;
        $tmp_arr = [];
        $fileData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/export/translit.txt');
        $fileData = mb_split("\n", mb_convert_encoding($fileData, 'utf-8', 'windows-1251'));

        array_pop($fileData);
        foreach ($fileData as $row => $value) {
            $temp = explode("\t", $value);
            if ($temp[0] !== '') {
                $tmp_arr[trim($temp[0])] = trim($temp[1]);
            }

        }

        $this->transliteratedWords = $tmp_arr;
        $this->mark_name = $markName;
        $this->modelName = $modelName;
    }

    public function getSotaVersions()
    { ?>
		<div class="re_miniblock"><h3>Выберите структуру ячеек</h3>
			<div class="krugleshki_s">
				<div class="re_circle_s" data-name="Классическая"
				     style="background-image: url('/style/rombik.png')"></div>
				<div class="re_circle_s" data-name="Вариант 2"
				     style="background-image: url('/style/protector2.png')"></div>
			</div>
		</div>
    <?php }


    /**
     * @param int $regionId
     * @return int|string
     */
    public function getRegName(int $regionId)
    {
        foreach ($this->regs as $regName => $regData) {
            if ($regData['id'] === $regionId) {
                return $regName;
            }
        }
    }

    /**
     * @param $string
     * @return string
     */
    public function templateReplace($string): string
    {
    	$mark = $this->markName;
    	$model = $this->modelName;
        $translit = [
                '{марка транслит}' => strtr($mark, $this->transliteratedWords),
                '{модель транслит}' => strtr($model, $this->transliteratedWords),
                '{модель}' => $model,
                '{марка}' => $mark,
                '{город в предложном падеже}' => $this->regs[$this->regionName]['city_pred'],
                '{город в винительном падеже}' => $this->regs[$this->regionName]['city_vin'],
                '{город в именительном падеже}' => $this->regs[$this->regionName]['city_imen'],
        ];
        return strtr($string, $translit);
    }

    public function getMainColor()
    { ?>
		<div class="re_miniblock"><h3>Выберите oсновной цвет</h3>
			<div class="krugleshki_osn color_fam1">
				<div class="re_circle_s img-1q" data-name="Красный" data-num="1"></div>
				<div class="re_circle_s img-2q" data-name="Оранжевый" data-num="2"></div>
				<div class="re_circle_s img-3q" data-name="Салатовый" data-num="3"></div>
				<div class="re_circle_s img-4q" data-name="Синий" data-num="4"></div>
				<div class="re_circle_s img-5q" data-name="Фиолетовый" data-num="5"></div>
				<div class="re_circle_s img-6q" data-name="Бежевый" data-num="6"></div>
				<div class="re_circle_s img-7q" data-name="Шоколадный" data-num="7"></div>
				<div class="re_circle_s img-8q" data-name="Светло серый" data-num="8"></div>
				<div class="re_circle_s img-9q" data-name="Серый" data-num="9"></div>
				<div class="re_circle_s img-10q" data-name="Черный" data-num="10"></div>
			</div>

			<div class="krugleshki_osn color_fam2 hidden">
				<div class="re_circle_s img-1q" data-name="Красный" data-num="1"></div>
				<div class="re_circle_s img-2m" data-name="Оранжевый" data-num="2"></div>
				<div class="re_circle_s img-3m" data-name="Салатовый" data-num="3"></div>
				<div class="re_circle_s img-4m" data-name="Синий" data-num="4"></div>
				<div class="re_circle_s img-5m" data-name="Темно синий" data-num="5"></div>
				<div class="re_circle_s img-6m" data-name="Фиолетовый" data-num="6"></div>
				<div class="re_circle_s img-7m" data-name="Бежевый" data-num="7"></div>
				<div class="re_circle_s img-8m" data-name="Шоколадный" data-num="8"></div>
				<div class="re_circle_s img-9m" data-name="Серый" data-num="9"></div>
				<div class="re_circle_s img-10m" data-name="Черный" data-num="10"></div>
			</div>

            <?php $this->getCoverPreview2(); ?>
		</div>
    <?php }

    public function getCoverPreview2()
    { ?>
		<div class="smotr">
			<img class="img_body" src="/style/material/10r.png" alt="">
			<img class="img_border" src="/style/borders/16b.png" alt="">
			<img class="img_logo" src="/style/shield.png" alt="" style="display: none;">
			<div class="re_clips">
				<img class="img_kreplenie" src="/style/clips.png" style="display: none;" alt="">
				<img class="img_kreplenie" src="/style/clips.png" style="display: none;" alt="">
			</div>
			<img class="img_pyatka" src="/style/podpatnik.png" style="display: none;" alt="">
		</div>
    <?php }

    public function getBorderColor()
    { ?>
		<div class="re_miniblock"><h3>Выберите цвет окантовки</h3>
			<div class="krugleshki_c">
				<div class="re_circle_s img-1" data-name="Бордовый" data-num="1"></div>
				<div class="re_circle_s img-2" data-name="Красный" data-num="2"></div>
				<div class="re_circle_s img-3" data-name="Оранжевый" data-num="3"></div>
				<div class="re_circle_s img-4" data-name="Желтый" data-num="4"></div>
				<div class="re_circle_s img-5" data-name="Салатовый" data-num="5"></div>
				<div class="re_circle_s img-6" data-name="Зеленый" data-num="6"></div>
				<div class="re_circle_s img-7" data-name="Темно зеленый" data-num="7"></div>
				<div class="re_circle_s img-8" data-name="Василек" data-num="8"></div>
				<div class="re_circle_s img-9" data-name="Синий" data-num="9"></div>
				<div class="re_circle_s img-10" data-name="Темно синий" data-num="10"></div>
				<div class="re_circle_s img-11" data-name="Бежевый" data-num="11"></div>
				<div class="re_circle_s img-12" data-name="Коричневый" data-num="12"></div>
				<div class="re_circle_s img-13" data-name="Белый" data-num="13"></div>
				<div class="re_circle_s img-14" data-name="Светло серый" data-num="14"></div>
				<div class="re_circle_s img-15" data-name="Серый" data-num="15"></div>
				<div class="re_circle_s img-16" data-name="Черный" data-num="16"></div>
			</div>
		</div>
    <?php }

    public function getCoverChanger($f_has_third_row, $f_bagage)
    { ?>
		<div class="re_miniblock kom">
			<div class="construct">
				<p class="checkboxes <? if ($f_has_third_row == "1") {
                    echo "checkboxes2 ";
                }; ?>checkboxesCover">
					<input type="checkbox" name="leftc" data-price="750" data-name="Коврик водителя" id="leftc"
					       data-p="1" alt="Водительский" checked><label for="leftc">Переднее водительское</label>
					<input type="checkbox" name="rightc" data-price="750" id="rightc" data-name="Коврик пассажира"
					       alt="пассажирский" data-p="2" checked><label for="rightc">Переднее пассажирское</label>
					<input type="checkbox" name="centerc" data-price="750" id="centerc"
					       data-name="Коврики для задних сидений" alt="на задние сидения" data-p="3" checked><label
							for="centerc">Задний ряд сидений</label>
					<input type="checkbox" name="tryadc" data-price="800" id="tryadc"
					       data-name="Коврики для третьего ряда" alt="Третий ряд" data-p="5"><label
							for="tryadc" <? if ($f_has_third_row !== "1") {
                        echo 'style="display: none"';
                    } ?>>Третий ряд сидений</label>
					<input type="checkbox" name="bagagec" <? if (!$f_bagage) {
                        echo 'hidden';
                    }; ?> data-price="1800" id="bagagec" data-name="Багажник" data-p="4"
					       alt="для багажника"><label <? if (!$f_bagage) {
                        echo 'style="display: none"';
                    }; ?> for="bagagec">Багажник <i>1800р</i></label>
				</p>

				<p class="visual-cons">
					<img src="/style/cover/11.png" alt="" data-p="1">
					<img src="/style/cover/21.png" alt="" data-p="2">
					<img src="/style/cover/31.png" alt="" data-p="3">
					<img src="/style/cover/50.png" alt="" data-p="5" <? if ($f_has_third_row !== "1") {
                        echo 'style="display: none"';
                    }; ?>>

					<img src="/style/cover/40.png" alt="" data-p="4" <? if (!$f_bagage) {
                        echo 'style="display: none"';
                    }; ?>>
				</p>
			</div>
		</div>
    <?php }

    public function getBreadcrumbs($catalogID, $parent_sub_tree)
    {
        if ($parent_sub_tree[2][Subdivision_ID] == $catalogID) { ?>

			<ol itemscope itemtype="http://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="<?= $parent_sub_tree [2][Hidden_URL] ?>"><span
								itemprop="name"><?= $parent_sub_tree [2][Subdivision_Name] ?></span></a>
					<meta itemprop="position" content="1"/>
				</li>
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="<?= $parent_sub_tree [1][Hidden_URL] ?>"><span
								itemprop="name"><?= $parent_sub_tree [1][Subdivision_Name] ?></span></a>
					<meta itemprop="position" content="2"/>
				</li>
				<li class="active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    <? $this->car_name = $parent_sub_tree [0][Subdivision_Name];
                    $this->mark_name = $parent_sub_tree [1][Subdivision_Name];
                    ?>
					<a itemprop="item" href="<?= $parent_sub_tree [0][Hidden_URL] ?>"
					   content="<?= $this->car_name; ?>"><span itemprop="name"><? if (strlen($this->car_name) >= 27) {
                                echo substr($this->car_name, 0, 27) . "..";
                            }
                            ?></span></a>
					<meta itemprop="position" content="3"/>
				</li>
			</ol> <?php } else { ?>
			<ol itemscope itemtype="http://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="<?= $parent_sub_tree [2][Hidden_URL] ?>"><span
								itemprop="name"><?= $parent_sub_tree [2][Subdivision_Name] ?></span></a>
					<meta itemprop="position" content="2"/>
				</li>
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="<?= $parent_sub_tree [1][Hidden_URL] ?>"><span
								itemprop="name"><?= $parent_sub_tree [1][Subdivision_Name] ?></span></a>
					<meta itemprop="position" content="3"/>
				</li>
				<li class="active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    <? $this->car_name = $parent_sub_tree [0][Subdivision_Name];
                    $this->mark_name = $parent_sub_tree [2][Subdivision_Name];
                    ?>
					<a itemprop="item" href="<?= $parent_sub_tree [0][Hidden_URL] ?>"><span
								itemprop="name"><?= $this->car_name ?></span></a>
					<meta itemprop="position" content="4"/>
				</li>
			</ol>
        <?php }
    }

    public function getH1()
    { ?>
		<h1><?= $this->mark_name ?> <?= $this->car_name ?></h1>
    <?php }

    public function getCarImage($catalogID)
    {
        global $nc_core;
        global $browse_path;
        global $parent_sub_tree;
        if ($parent_sub_tree [2][Subdivision_ID] == $catalogID) {
            ?>
			<img src="/style/cars/<?= $this->mark_name ?>/<?= $this->car_name ?>.jpg"
			     alt="<?= $this->mark_name ?> <?= $this->car_name ?>"
			     title="<?= $this->mark_name ?><?= $this->car_name ?>">
            <?php
        } else { ?>
			<img src="/style/cars/<?= $parent_sub_tree [2][Subdivision_Name] ?>/<?= $parent_sub_tree [1][Subdivision_Name] ?>/<?= $this->car_name ?>.jpg"
			     alt="<?= $this->mark_name ?> <?= $this->car_name ?>"
			     title="<?= $this->mark_name ?> <?= $this->car_name ?>">
        <?php }
        ?>

		<!--            <img src="/style/cars/--><?//= $this->mark_name
        ?><!--/--><?//= $this-karusel_photo>car_name
        ?><!--.jpg"-->
		<!--                 alt="--><?//= $this->mark_name
        ?><!----><?//= $this->car_name
        ?><!--" title="--><?//= $this->mark_name
        ?><!----><?//= $this->car_name
        ?><!--">-->
        <?php
    }

    public function getItogo($f_status)
    { ?>
		<div class="buy_kovrik">
			<p>Итого:</p>
			<span class="price_el" data-summ="2200" data-skid="<?= ($_COOKIE["r"]) ? $_COOKIE["r"] : 0 ?>">
                            <?
                            if ($f_status == 0) {
                                echo "Нет в наличии";
                            } else {
                                if ($_COOKIE["r"]) {
                                    echo "2200.";
                                } else {
                                    echo "2200р.";
                                }
                            } ?>
                        </span>
			<a class="re_btnBuy"
			   data-status="<?= $f_status ?>"><?= ($f_status == 0) ? 'Заказать' : 'Перейти к оформлению' ?></a>
			<a class="one_click">Купить в один клик</a>
		</div>
    <?php }

    public function getCoverPreview()
    { ?>
		<div class="smotr preview_block2">
			<img class="img_body img_body2" src="/style/material/10r.png" alt="">
			<img class="img_border img_border2" src="/style/borders/16b.png" alt="">
			<img class="img_logo img_logo2" src="/style/shield.png" alt="" style="display: none;">
			<div class="re_clips re_clips2">
				<img class="img_kreplenie img_kreplenie2" src="/style/clips.png" style="display: none;" alt="">
				<img class="img_kreplenie img_kreplenie2" src="/style/clips.png" style="display: none;" alt="">
			</div>
			<img class="img_pyatka img_pyatka2" src="/style/podnozhka.png" style="display: none;" alt="">
		</div>

		<!-- Всплывашка -->
		<style>
			.fancybox-inner .re_kovrik_top {
				zoom: 2;
				padding: 18px;
			}

			.fancybox-button {
				width: 20px;
				height: 20px;
				cursor: pointer;
			}

			.fancybox-button:hover svg path {
				fill: red;
			}

			.fancybox-button svg path {
				fill: black;
			}

			.fancybox-slide--html .fancybox-close-small {
				padding: 0px;
			}

			.fancybox-content {
				1 width: auto;
				background-color: #f2f2f2;
				border-radius: 10px;
			}

			.fancybox-inner .preview_block2 {
				1 width: 0% !important;
			}

			.fancybox-inner .buy_kovrik {
				display: none
			}

			.smotr.preview_block2 {
				cursor: pointer;
			}

			.fancybox-content {
				max-width: 180px;
				margin: 0 auto;
			}

			.fancybox-slide {
				height: auto;
			}
		</style>

		<!-- Всплывашка -->

    <?php }

    public function getDops()
    { ?>
		<div class="dop_option">
			<div class="option_left">
				<div class="construct dopusl">
					<p class="checkboxes checkboxesDop">
						<input type="checkbox" name="podpyat" data-name="Подпятник" data-price="600" id="podpyat"
						       alt="Подпятник">
						<label for="podpyat"> Подпятник </label>
						<span class="about">
                                    <img src="/style/proiz/photo_2019-01-26_12-49-07.jpg" alt="Подпятник">
                                     <span></span>
                                     <span>Подпятник</span>
                                     <span>Подпятник специально предназначен для меньшего износа основного материала водительского автоковрика EVA (ЭВА) и придает индивидуальность вашему авто. Особенно актуален для водителей женщин, предпочитающих каблуки. Монтируется самим водителем с помощью отвертки (каждый водитель имеет индивидуальный размер ноги и положение при посадке в автомобиле). В комплекте: Подпятник и 4 крепежа. </span>
                                     <span> Вид подпятника, уточняйте у оператора.</span>
                        </span>
						<input type="checkbox" name="clips" data-name="Клипсы для креплений" data-price="100"
						       id="clips" alt="Клипсы">
						<label class="shtat" for="clips">Есть штатные места для креплений</label>
						<span class="about">
                                    <img src="/style/proiz/ab5.jpg" alt="">
                                    <span></span>
                                    <span>Клипсы</span>
                                    <span> На некоторых автомобилях предусмотрены специальные крепления для ковриков. Для в такие салоны, необходимо что бы коврики имели специальные клипсы под эти крепления. Мы устанавливаем их на специальном оборудовании под эту задачу, ровно и красиво.</span>
                                    <span> Стоимость услуги включая цену клипс: 100 руб.</span>
                                </span>
					</p>
				</div>
			</div>
			<div class="option_right">

				<p class="checkboxes checkboxesDop">
                        <span class="shield-box">
                            <input type="checkbox" class="shield_s" name="shield" data-name="Шильдики" data-price="350"
                                   data-val="2" data-type="1" id="shield" alt="Шильдики">
                            <label for="shield">Шильдики </label>
                            <span class="vpm_vibor">
                                <span class="varik">Вариант</span>
                                <a class="v_vibor act">S</a>
                                <a class="v_vibor">XL</a>
                                <span class="p_m_block">
                                    <span class="minus">-</span>
                                    <span class="coll_vo">2</span>
                                    <span class="plus">+</span>
                                    <span class="coll_vo_s">шт</span>
                                </span>
                            </span>
                        </span>
					<span class="about">
                            <img src="/style/proiz/ab3.jpg" alt="">
                            <img src="/style/proiz/ab4.jpg" alt="">
                            <span>Шильдик металлический</span>
                            <span> Два шильдика металлических с гравировкой автопроизводителя для автоковриков EVA (ЭВА). Устанавливаются водителем в любом понравившемся месте коврика с помощью отвёртки. Комплект: Шильдик металлический, два крепежа.</span>
                            <span> Продаются от 2 штук.</span>
                        </span>
				</p>
			</div>
		</div>
    <?php }

    public function getTabs($f_salonIMG, $f_ModelDesc)
    { ?>
		<div class="re_tabs_block">
			<div class="re_btnTabs">
				<a class="btnTabs">Описание</a>
				<a class="btnTabs">Характеристики</a>
				<a class="btnTabs act">Фото</a>
				<a class="btnTabs">Доставка</a>
				<a class="btnTabs">Отзывы</a>
			</div>
			<div class="tabs_content">
				<?php
				$modelDesc = $f_ModelDesc;
				if ($modelDesc === '' || $modelDesc === null) {
					$modelDesc = $this->templateReplace($this->productDescTemplate);
				}
				?>
				<div class="t_cont" style="display: none">
                    <?= htmlspecialchars_decode($modelDesc); ?>
				</div>
				<!--noindex-->
				<div class="t_cont" style="display: none;height: calc(100% - 36px);">
					<ul style="overflow-x: auto;
height: 100%;">
						<li>- Материал эластичен даже в морозы.</li>
						<li>- Гипоаллергенный, экологичный.</li>
						<li>- Ячеистая структура удерживает воду, снег, грязь и песок.</li>
						<li>- Легче всех видов ковриков в разы.</li>
						<li>- Легко моются, благодаря плохой адгезии с грязью.</li>
						<li>- Широкая цветовая гамма.</li>
						<li>- Эстетичность салона, комфорт, автоковрики EVA придают индивидуальность автомобилю.</li>
						<li>- Не источают запах.</li>
						<li>- Не портят обувь и брюки.</li>
						<li>- Не боятся агрессивные среды (масла, соли, технические жидкости).</li>
						<li>- Имеют липучки либо клипсы, из за чего не ерзают по салону.</li>
						<li>- Автоковрики из EVA совмещают в себе плюсы всех видов ковриков.</li>
					</ul>
				</div>
				<!--/noindex-->
				<div class="t_cont" style="display: block">
					<div class="karusel_photo">
                        <?
                        $arr = explode(",", $f_salonIMG);
                        $st = 0;
                        foreach ($arr as $value) {
                            if ($value !== "") {
                                echo '<img data-src="' . $value . '" src="' . $value . '" alt="Коврики в салон ' . $this->mark_name, $this->car_name . '" title = "Коврики в салон ' . $this->mark_name, $this->car_name . '" >';
                                $st++;
                            }
                        } ?>
					</div>
				</div>
				<div class="t_cont">

					<ul style="overflow-x: auto;
height: 100%;">
						<li>- ТК GTD (КИТ) от 185 рублей*</li>
						<li> - Почта России 400 рублей</li>
						<li>- ТК ПЭК от 400 рублей*</li>
						<li>- Иная ТК (по согласованию)*</li>
						<li>- Наложенным платежом Почта России предоплата 500 рублей</li>
					</ul>

					<p style="margin-top: 10px;">* Доставка осуществляется до терминала ТК в вашем городе.</p>
					<p>Оплата доставки при получении посылки в ТК.</p>

				</div>

				<div class="t_cont">

					<div class="review_header">
						<button>Оставить отзыв</button>
					</div>

                    <?php
                    $product_reviews = $this->reviews->getSubdivisionReview($GLOBALS['current_sub']['Subdivision_ID']);
                    if (count($product_reviews) === 0) { ?>
						<div class="clear_reviews">
							<p>

								Отзывов о данном коврике еще нет.
							</p>
							<p>
								Возможно вы станете первым
							</p>
						</div>
                    <?php }
                    foreach ($product_reviews as $review) { ?>
						<div class="one_review">
							<div class="one_review_header">
								<div class="one_review_name_and_rating">
									<span class="review_author_name"><?= $review['name'] ?></span>
									<div class="review_rating">
                                        <?php for ($i = 0; $i < 5; $i++) { ?>
											<span <?= (intval($review['rating']) > $i) ? 'class="act"' : '' ?>></span>
                                        <?php } ?>
									</div>
								</div>
								<div class="one_review_date">
                                    <?= date('d.m.Y', strtotime($review['create_date'])) ?>
								</div>
							</div>

							<div class="one_review_text">
                                <?= $review['review_text'] ?>
							</div>
						</div>
                    <?php } ?>
				</div>
			</div>
		</div>

		<div class="overlay_2"></div>
		<form action="<?= $GLOBALS['current_sub']['Hidden_URL'] ?>?add_review" method="post"
		      class="send_review_modal">
			<div class="modal_close_button"></div>
			<div class="modal_h2">Оставить отзыв</div>

			<div class="modal_review_desc">
				Обращаем ваше внимание на то, что ваш отзыв будет добавлен после проверки модератором
			</div>

			<div class="modal_input_box">
				<label for="modal_user_name" class="modal_input_label">Ваше имя</label>
				<input id="modal_user_name" type="text" name="modal_user_name" required>
			</div>

			<div class="modal_input_box">
				<label for="modal_user_email" class="modal_input_label">Ваш email
					<span>(не будет опубликован)</span></label>
				<input id="modal_user_email" type="text" name="modal_user_email" required>
			</div>

			<div class="modal_input_box">
				<label for="modal_user_review" class="modal_input_label">Ваше отзыв</label>
				<textarea name="modal_user_review" id="modal_user_review" rows="2"></textarea>
			</div>

			<div class="modal_rating_block">
				<label class="modal_input_label">
					Ваша оценка
				</label>
				<div class="modal_review_rating" id="write_review_rating">
					<span class="act" data-rating="1"></span>
					<span class="act" data-rating="2"></span>
					<span class="act" data-rating="3"></span>
					<span class="act" data-rating="4"></span>
					<span data-rating="5"></span>
					<input type="text" hidden name="modal_user_rating" value="4">
				</div>

			</div>

			<button>Оставить отзыв</button>

			<div class="modal_accept_rules">
				Нажимая кнопку, я даю согласие на обработку
				персональных данных
			</div>
		</form>
    <?php }

    public function getOplata()
    { ?>

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.2/jquery.fancybox.min.css">
		<script>
            $(function () {
                $('body').append('<script src="\/js\/jquery.fancybox.min.js"><\/script>');
            });
            $('.smotr.preview_block2').on('click', function () {
                $.fancybox.open($(this).parent().parent().html());
            });
		</script>

		<div class="oplata_block">
			<div class="sposob_dostavki">
				<h3>Способ доставки</h3>
				<div class="dostavka_plashka dostavka_el samoVivos hidden" data-dos="3"><strong>Самовывоз
					</strong>
					<p class="text_dostavka">г. Набережные Челны,ул. Орловская, д. 73а.</p>
				</div>
				<div class="dostavka_plashka dostavka_el russian-post" data-dos="2"><strong>Почта России</strong>
					<p class="text_dostavka act">Cтоимость доставки 400 рублей, срок до 10 дней в зависимости от региона
						доставки.</p>
				</div>
				<div class="dostavka_plashka dostavka_el act tkPec" data-dos="1"><strong>ТК "ПЭК"</strong>
					<p class="text_dostavka">Введите адрес для уточнения сроков и стоимости доставки. В среднем
						стоимость доставки - 400р</p>
				</div>
				<div class="dostavka_plashka dostavka_el tkKit" data-dos="1"><strong>ТК "КИТ"</strong>
					<p class="text_dostavka">Введите адрес для уточнения сроков и стоимости доставки.</p>
				</div>
				<div class="dostavka_plashka dostavka_el" data-dos="1"><strong>Другая транспортная компания
					</strong>
					<p class="text_dostavka">Согласуется в индивидуальном порядке с менеджером.</p>
				</div>

				<a class="nazad back1">Назад</a>
			</div>
			<div class="dannye_user">
				<h3>Данные получателя</h3>
				<form class="user_white" id="page1-form" action="" method="post">
					<input type="text" name="name" placeholder="Ф.И.О">
					<input class="order_phone" type="text" name="number" placeholder="+7 (___) ___-____">
					<input type="email" name="email" placeholder="E-mail">
					<input type="text" id="address" name="adress" placeholder="Адрес">
					<input class="inactive" type="text" name="post-index" placeholder="Почтовый индекс">
					<input type="text" name="zakaz" id="zakazI" hidden reqired>

					<div class="bottom_user">
                        <span class="pod_knopkoi user">Нажимая кнопку «Далее», я даю согласие
на обработку <a href="/politika-konfidentsialnosti/">персональных данных</a></span>
						<a class="dalee next_1">Далее</a>
					</div>
				</form>
			</div>
		</div>

    <?php }


}