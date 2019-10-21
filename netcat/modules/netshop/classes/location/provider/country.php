<?php

/**
 * Класс-заглушка для адресов за пределами России (принимает любой адрес, если он
 * начинается с названия страны)
 */
class nc_netshop_location_provider_country extends nc_netshop_location_provider {

    const WEIGHT_EXACT_NAME = 50;
    const WEIGHT_NAME_STARTS_WITH = 25;
    const WEIGHT_OTHER_NAME_STARTS_WITH = 15;
    const WEIGHT_DEFAULT = 5;

    static protected $countries_converted = false;
    /** @var array */
    static protected $countries = array(
        'АБХАЗИЯ' =>
            array(
                'name' => 'Абхазия',
                'english' => 'ABKHAZIA',
                'numeric_code' => '895',
            ),
        'АВСТРАЛИЯ' =>
            array(
                'name' => 'Австралия',
                'english' => 'AUSTRALIA',
                'numeric_code' => '036',
            ),
        'АВСТРИЯ' =>
            array(
                'name' => 'Австрия',
                'english' => 'AUSTRIA',
                'numeric_code' => '040',
            ),
        'АЗЕРБАЙДЖАН' =>
            array(
                'name' => 'Азербайджан',
                'english' => 'AZERBAIJAN',
                'numeric_code' => '031',
            ),
        'АЛБАНИЯ' =>
            array(
                'name' => 'Албания',
                'english' => 'ALBANIA',
                'numeric_code' => '008',
            ),
        'АЛЖИР' =>
            array(
                'name' => 'Алжир',
                'english' => 'ALGERIA',
                'numeric_code' => '012',
            ),
        'АМЕРИКАНСКОЕ САМОА' =>
            array(
                'name' => 'Американское Самоа',
                'english' => 'AMERICAN SAMOA',
                'numeric_code' => '016',
            ),
        'АНГИЛЬЯ' =>
            array(
                'name' => 'Ангилья',
                'english' => 'ANGUILLA',
                'numeric_code' => '660',
            ),
        'АНГОЛА' =>
            array(
                'name' => 'Ангола',
                'english' => 'ANGOLA',
                'numeric_code' => '024',
            ),
        'АНДОРРА' =>
            array(
                'name' => 'Андорра',
                'english' => 'ANDORRA',
                'numeric_code' => '020',
            ),
        'АНТАРКТИДА' =>
            array(
                'name' => 'Антарктида',
                'english' => 'ANTARCTICA',
                'numeric_code' => '010',
            ),
        'АНТИГУА И БАРБУДА' =>
            array(
                'name' => 'Антигуа и Барбуда',
                'english' => 'ANTIGUA AND BARBUDA',
                'numeric_code' => '028',
            ),
        'АРГЕНТИНА' =>
            array(
                'name' => 'Аргентина',
                'english' => 'ARGENTINA',
                'numeric_code' => '032',
            ),
        'АРМЕНИЯ' =>
            array(
                'name' => 'Армения',
                'english' => 'ARMENIA',
                'numeric_code' => '051',
            ),
        'АРУБА' =>
            array(
                'name' => 'Аруба',
                'english' => 'ARUBA',
                'numeric_code' => '533',
            ),
        'АФГАНИСТАН' =>
            array(
                'name' => 'Афганистан',
                'english' => 'AFGHANISTAN',
                'numeric_code' => '004',
            ),
        'БАГАМЫ' =>
            array(
                'name' => 'Багамы',
                'english' => 'BAHAMAS',
                'numeric_code' => '044',
            ),
        'БАНГЛАДЕШ' =>
            array(
                'name' => 'Бангладеш',
                'english' => 'BANGLADESH',
                'numeric_code' => '050',
            ),
        'БАРБАДОС' =>
            array(
                'name' => 'Барбадос',
                'english' => 'BARBADOS',
                'numeric_code' => '052',
            ),
        'БАХРЕЙН' =>
            array(
                'name' => 'Бахрейн',
                'english' => 'BAHRAIN',
                'numeric_code' => '048',
            ),
        'БЕЛАРУСЬ' =>
            array(
                'name' => 'Беларусь',
                'english' => 'BELARUS',
                'numeric_code' => '112',
            ),
        'БЕЛИЗ' =>
            array(
                'name' => 'Белиз',
                'english' => 'BELIZE',
                'numeric_code' => '084',
            ),
        'БЕЛЬГИЯ' =>
            array(
                'name' => 'Бельгия',
                'english' => 'BELGIUM',
                'numeric_code' => '056',
            ),
        'БЕНИН' =>
            array(
                'name' => 'Бенин',
                'english' => 'BENIN',
                'numeric_code' => '204',
            ),
        'БЕРМУДЫ' =>
            array(
                'name' => 'Бермуды',
                'english' => 'BERMUDA',
                'numeric_code' => '060',
            ),
        'БОЛГАРИЯ' =>
            array(
                'name' => 'Болгария',
                'english' => 'BULGARIA',
                'numeric_code' => '100',
            ),
        'БОЛИВИЯ' =>
            array(
                'name' => 'Боливия',
                'english' => 'BOLIVIA, PLURINATIONAL STATE OF',
                'numeric_code' => '068',
            ),
        'КАРИБСКИЕ НИДЕРЛАНДЫ' =>
            array(
                'name' => 'Карибские Нидерланды',
                'english' => 'BONAIRE, SINT EUSTATIUS AND SABA',
                'numeric_code' => '535',
            ),
        'БОСНИЯ И ГЕРЦЕГОВИНА' =>
            array(
                'name' => 'Босния и Герцеговина',
                'english' => 'BOSNIA AND HERZEGOVINA',
                'numeric_code' => '070',
            ),
        'БОТСВАНА' =>
            array(
                'name' => 'Ботсвана',
                'english' => 'BOTSWANA',
                'numeric_code' => '072',
            ),
        'БРАЗИЛИЯ' =>
            array(
                'name' => 'Бразилия',
                'english' => 'BRAZIL',
                'numeric_code' => '076',
            ),
        'БРИТАНСКАЯ ТЕРРИТОРИЯ В ИНДИЙСКОМ ОКЕАНЕ' =>
            array(
                'name' => 'Британская территория в Индийском океане',
                'english' => 'BRITISH INDIAN OCEAN TERRITORY',
                'numeric_code' => '086',
            ),
        'БРУНЕЙ-ДАРУССАЛАМ' =>
            array(
                'name' => 'Бруней-Даруссалам',
                'english' => 'BRUNEI DARUSSALAM',
                'numeric_code' => '096',
            ),
        'БУРКИНА-ФАСО' =>
            array(
                'name' => 'Буркина-Фасо',
                'english' => 'BURKINA FASO',
                'numeric_code' => '854',
            ),
        'БУРУНДИ' =>
            array(
                'name' => 'Бурунди',
                'english' => 'BURUNDI',
                'numeric_code' => '108',
            ),
        'БУТАН' =>
            array(
                'name' => 'Бутан',
                'english' => 'BHUTAN',
                'numeric_code' => '064',
            ),
        'ВАНУАТУ' =>
            array(
                'name' => 'Вануату',
                'english' => 'VANUATU',
                'numeric_code' => '548',
            ),
        'ВЕНГРИЯ' =>
            array(
                'name' => 'Венгрия',
                'english' => 'HUNGARY',
                'numeric_code' => '348',
            ),
        'ВЕНЕСУЭЛА' =>
            array(
                'name' => 'Венесуэла',
                'english' => 'VENEZUELA',
                'numeric_code' => '862',
            ),
        'ВИРГИНСКИЕ ОСТРОВА (БРИТАНСКИЕ)' =>
            array(
                'name' => 'Виргинские острова (Британские)',
                'english' => 'VIRGIN ISLANDS, BRITISH',
                'numeric_code' => '092',
            ),
        'ВИРГИНСКИЕ ОСТРОВА (США)' =>
            array(
                'name' => 'Виргинские острова (США)',
                'english' => 'VIRGIN ISLANDS, U.S.',
                'numeric_code' => '850',
            ),
        'ВЬЕТНАМ' =>
            array(
                'name' => 'Вьетнам',
                'english' => 'VIETNAM',
                'numeric_code' => '704',
            ),
        'ГАБОН' =>
            array(
                'name' => 'Габон',
                'english' => 'GABON',
                'numeric_code' => '266',
            ),
        'ГАИТИ' =>
            array(
                'name' => 'Гаити',
                'english' => 'HAITI',
                'numeric_code' => '332',
            ),
        'ГАЙАНА' =>
            array(
                'name' => 'Гайана',
                'english' => 'GUYANA',
                'numeric_code' => '328',
            ),
        'ГАМБИЯ' =>
            array(
                'name' => 'Гамбия',
                'english' => 'GAMBIA',
                'numeric_code' => '270',
            ),
        'ГАНА' =>
            array(
                'name' => 'Гана',
                'english' => 'GHANA',
                'numeric_code' => '288',
            ),
        'ГВАДЕЛУПА' =>
            array(
                'name' => 'Гваделупа',
                'english' => 'GUADELOUPE',
                'numeric_code' => '312',
            ),
        'ГВАТЕМАЛА' =>
            array(
                'name' => 'Гватемала',
                'english' => 'GUATEMALA',
                'numeric_code' => '320',
            ),
        'ГВИНЕЯ' =>
            array(
                'name' => 'Гвинея',
                'english' => 'GUINEA',
                'numeric_code' => '324',
            ),
        'ГВИНЕЯ-БИСАУ' =>
            array(
                'name' => 'Гвинея-Бисау',
                'english' => 'GUINEA-BISSAU',
                'numeric_code' => '624',
            ),
        'ГЕРМАНИЯ' =>
            array(
                'name' => 'Германия',
                'english' => 'GERMANY',
                'numeric_code' => '276',
            ),
        'ГЕРНСИ' =>
            array(
                'name' => 'Гернси',
                'english' => 'GUERNSEY',
                'numeric_code' => '831',
            ),
        'ГИБРАЛТАР' =>
            array(
                'name' => 'Гибралтар',
                'english' => 'GIBRALTAR',
                'numeric_code' => '292',
            ),
        'ГОНДУРАС' =>
            array(
                'name' => 'Гондурас',
                'english' => 'HONDURAS',
                'numeric_code' => '340',
            ),
        'ГОНКОНГ' =>
            array(
                'name' => 'Гонконг',
                'english' => 'HONG KONG',
                'numeric_code' => '344',
            ),
        'ГРЕНАДА' =>
            array(
                'name' => 'Гренада',
                'english' => 'GRENADA',
                'numeric_code' => '308',
            ),
        'ГРЕНЛАНДИЯ' =>
            array(
                'name' => 'Гренландия',
                'english' => 'GREENLAND',
                'numeric_code' => '304',
            ),
        'ГРЕЦИЯ' =>
            array(
                'name' => 'Греция',
                'english' => 'GREECE',
                'numeric_code' => '300',
            ),
        'ГРУЗИЯ' =>
            array(
                'name' => 'Грузия',
                'english' => 'GEORGIA',
                'numeric_code' => '268',
            ),
        'ГУАМ' =>
            array(
                'name' => 'Гуам',
                'english' => 'GUAM',
                'numeric_code' => '316',
            ),
        'ДАНИЯ' =>
            array(
                'name' => 'Дания',
                'english' => 'DENMARK',
                'numeric_code' => '208',
            ),
        'ДЖЕРСИ' =>
            array(
                'name' => 'Джерси',
                'english' => 'JERSEY',
                'numeric_code' => '832',
            ),
        'ДЖИБУТИ' =>
            array(
                'name' => 'Джибути',
                'english' => 'DJIBOUTI',
                'numeric_code' => '262',
            ),
        'ДОМИНИКА' =>
            array(
                'name' => 'Доминика',
                'english' => 'DOMINICA',
                'numeric_code' => '212',
            ),
        'ДОМИНИКАНСКАЯ РЕСПУБЛИКА' =>
            array(
                'name' => 'Доминиканская Республика',
                'english' => 'DOMINICAN REPUBLIC',
                'numeric_code' => '214',
            ),
        'ЕГИПЕТ' =>
            array(
                'name' => 'Египет',
                'english' => 'EGYPT',
                'numeric_code' => '818',
            ),
        'ЗАМБИЯ' =>
            array(
                'name' => 'Замбия',
                'english' => 'ZAMBIA',
                'numeric_code' => '894',
            ),
        'ЗАПАДНАЯ САХАРА' =>
            array(
                'name' => 'Западная Сахара',
                'english' => 'WESTERN SAHARA',
                'numeric_code' => '732',
            ),
        'ЗИМБАБВЕ' =>
            array(
                'name' => 'Зимбабве',
                'english' => 'ZIMBABWE',
                'numeric_code' => '716',
            ),
        'ИЗРАИЛЬ' =>
            array(
                'name' => 'Израиль',
                'english' => 'ISRAEL',
                'numeric_code' => '376',
            ),
        'ИНДИЯ' =>
            array(
                'name' => 'Индия',
                'english' => 'INDIA',
                'numeric_code' => '356',
            ),
        'ИНДОНЕЗИЯ' =>
            array(
                'name' => 'Индонезия',
                'english' => 'INDONESIA',
                'numeric_code' => '360',
            ),
        'ИОРДАНИЯ' =>
            array(
                'name' => 'Иордания',
                'english' => 'JORDAN',
                'numeric_code' => '400',
            ),
        'ИРАК' =>
            array(
                'name' => 'Ирак',
                'english' => 'IRAQ',
                'numeric_code' => '368',
            ),
        'ИРАН' =>
            array(
                'name' => 'Иран',
                'english' => 'IRAN, ISLAMIC REPUBLIC OF',
                'numeric_code' => '364',
            ),
        'ИРЛАНДИЯ' =>
            array(
                'name' => 'Ирландия',
                'english' => 'IRELAND',
                'numeric_code' => '372',
            ),
        'ИСЛАНДИЯ' =>
            array(
                'name' => 'Исландия',
                'english' => 'ICELAND',
                'numeric_code' => '352',
            ),
        'ИСПАНИЯ' =>
            array(
                'name' => 'Испания',
                'english' => 'SPAIN',
                'numeric_code' => '724',
            ),
        'ИТАЛИЯ' =>
            array(
                'name' => 'Италия',
                'english' => 'ITALY',
                'numeric_code' => '380',
            ),
        'ЙЕМЕН' =>
            array(
                'name' => 'Йемен',
                'english' => 'YEMEN',
                'numeric_code' => '887',
            ),
        'КАБО-ВЕРДЕ' =>
            array(
                'name' => 'Кабо-Верде',
                'english' => 'CAPE VERDE',
                'numeric_code' => '132',
            ),
        'КАЗАХСТАН' =>
            array(
                'name' => 'Казахстан',
                'english' => 'KAZAKHSTAN',
                'numeric_code' => '398',
            ),
        'КАМБОДЖА' =>
            array(
                'name' => 'Камбоджа',
                'english' => 'CAMBODIA',
                'numeric_code' => '116',
            ),
        'КАМЕРУН' =>
            array(
                'name' => 'Камерун',
                'english' => 'CAMEROON',
                'numeric_code' => '120',
            ),
        'КАНАДА' =>
            array(
                'name' => 'Канада',
                'english' => 'CANADA',
                'numeric_code' => '124',
            ),
        'КАТАР' =>
            array(
                'name' => 'Катар',
                'english' => 'QATAR',
                'numeric_code' => '634',
            ),
        'КЕНИЯ' =>
            array(
                'name' => 'Кения',
                'english' => 'KENYA',
                'numeric_code' => '404',
            ),
        'КИПР' =>
            array(
                'name' => 'Кипр',
                'english' => 'CYPRUS',
                'numeric_code' => '196',
            ),
        'КИРГИЗИЯ' =>
            array(
                'name' => 'Киргизия',
                'english' => 'KYRGYZSTAN',
                'numeric_code' => '417',
            ),
        'КИРИБАТИ' =>
            array(
                'name' => 'Кирибати',
                'english' => 'KIRIBATI',
                'numeric_code' => '296',
            ),
        'КИТАЙ' =>
            array(
                'name' => 'Китай',
                'english' => 'CHINA',
                'numeric_code' => '156',
            ),
        'КОКОСОВЫЕ (КИЛИНГ) ОСТРОВА' =>
            array(
                'name' => 'Кокосовые (Килинг) острова',
                'english' => 'COCOS (KEELING) ISLANDS',
                'numeric_code' => '166',
            ),
        'КОЛУМБИЯ' =>
            array(
                'name' => 'Колумбия',
                'english' => 'COLOMBIA',
                'numeric_code' => '170',
            ),
        'КОМОРЫ' =>
            array(
                'name' => 'Коморы',
                'english' => 'COMOROS',
                'numeric_code' => '174',
            ),
        'КОНГО' =>
            array(
                'name' => 'Конго',
                'english' => 'CONGO',
                'numeric_code' => '178',
            ),
        'ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА КОНГО' =>
            array(
                'name' => 'Демократическая Республика Конго',
                'english' => 'CONGO, DEMOCRATIC REPUBLIC OF THE',
                'numeric_code' => '180',
            ),
        'КОРЕЙСКАЯ НАРОДНО-ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА' =>
            array(
                'name' => 'Корейская Народно-Демократическая Республика',
                'english' => 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF',
                'numeric_code' => '408',
            ),
        'РЕСПУБЛИКА КОРЕЯ' =>
            array(
                'name' => 'Республика Корея',
                'english' => 'KOREA, REPUBLIC OF',
                'numeric_code' => '410',
            ),
        'КОСТА-РИКА' =>
            array(
                'name' => 'Коста-Рика',
                'english' => 'COSTA RICA',
                'numeric_code' => '188',
            ),
        'КОТ Д\'ИВУАР' =>
            array(
                'name' => 'Кот д\'Ивуар',
                'english' => 'COTE D\'IVOIRE',
                'numeric_code' => '384',
            ),
        'КУБА' =>
            array(
                'name' => 'Куба',
                'english' => 'CUBA',
                'numeric_code' => '192',
            ),
        'КУВЕЙТ' =>
            array(
                'name' => 'Кувейт',
                'english' => 'KUWAIT',
                'numeric_code' => '414',
            ),
        'КЮРАСАО' =>
            array(
                'name' => 'Кюрасао',
                'english' => 'CURAÇAO',
                'numeric_code' => '531',
            ),
        'ЛАОС' =>
            array(
                'name' => 'Лаос',
                'english' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
                'numeric_code' => '418',
            ),
        'ЛАТВИЯ' =>
            array(
                'name' => 'Латвия',
                'english' => 'LATVIA',
                'numeric_code' => '428',
            ),
        'ЛЕСОТО' =>
            array(
                'name' => 'Лесото',
                'english' => 'LESOTHO',
                'numeric_code' => '426',
            ),
        'ЛИВАН' =>
            array(
                'name' => 'Ливан',
                'english' => 'LEBANON',
                'numeric_code' => '422',
            ),
        'ЛИВИЙСКАЯ АРАБСКАЯ ДЖАМАХИРИЯ' =>
            array(
                'name' => 'Ливийская Арабская Джамахирия',
                'english' => 'LIBYAN ARAB JAMAHIRIYA',
                'numeric_code' => '434',
            ),
        'ЛИБЕРИЯ' =>
            array(
                'name' => 'Либерия',
                'english' => 'LIBERIA',
                'numeric_code' => '430',
            ),
        'ЛИХТЕНШТЕЙН' =>
            array(
                'name' => 'Лихтенштейн',
                'english' => 'LIECHTENSTEIN',
                'numeric_code' => '438',
            ),
        'ЛИТВА' =>
            array(
                'name' => 'Литва',
                'english' => 'LITHUANIA',
                'numeric_code' => '440',
            ),
        'ЛЮКСЕМБУРГ' =>
            array(
                'name' => 'Люксембург',
                'english' => 'LUXEMBOURG',
                'numeric_code' => '442',
            ),
        'МАВРИКИЙ' =>
            array(
                'name' => 'Маврикий',
                'english' => 'MAURITIUS',
                'numeric_code' => '480',
            ),
        'МАВРИТАНИЯ' =>
            array(
                'name' => 'Мавритания',
                'english' => 'MAURITANIA',
                'numeric_code' => '478',
            ),
        'МАДАГАСКАР' =>
            array(
                'name' => 'Мадагаскар',
                'english' => 'MADAGASCAR',
                'numeric_code' => '450',
            ),
        'МАЙОТТА' =>
            array(
                'name' => 'Майотта',
                'english' => 'MAYOTTE',
                'numeric_code' => '175',
            ),
        'МАКАО' =>
            array(
                'name' => 'Макао',
                'english' => 'MACAO',
                'numeric_code' => '446',
            ),
        'МАЛАВИ' =>
            array(
                'name' => 'Малави',
                'english' => 'MALAWI',
                'numeric_code' => '454',
            ),
        'МАЛАЙЗИЯ' =>
            array(
                'name' => 'Малайзия',
                'english' => 'MALAYSIA',
                'numeric_code' => '458',
            ),
        'МАЛИ' =>
            array(
                'name' => 'Мали',
                'english' => 'MALI',
                'numeric_code' => '466',
            ),
        'МАЛЫЕ ТИХООКЕАНСКИЕ ОТДАЛЕННЫЕ ОСТРОВА СОЕДИНЕННЫХ ШТАТОВ' =>
            array(
                'name' => 'Малые Тихоокеанские отдаленные острова Соединенных Штатов',
                'english' => 'UNITED STATES MINOR OUTLYING ISLANDS',
                'numeric_code' => '581',
            ),
        'МАЛЬДИВЫ' =>
            array(
                'name' => 'Мальдивы',
                'english' => 'MALDIVES',
                'numeric_code' => '462',
            ),
        'МАЛЬТА' =>
            array(
                'name' => 'Мальта',
                'english' => 'MALTA',
                'numeric_code' => '470',
            ),
        'МАРОККО' =>
            array(
                'name' => 'Марокко',
                'english' => 'MOROCCO',
                'numeric_code' => '504',
            ),
        'МАРТИНИКА' =>
            array(
                'name' => 'Мартиника',
                'english' => 'MARTINIQUE',
                'numeric_code' => '474',
            ),
        'МАРШАЛЛОВЫ ОСТРОВА' =>
            array(
                'name' => 'Маршалловы острова',
                'english' => 'MARSHALL ISLANDS',
                'numeric_code' => '584',
            ),
        'МЕКСИКА' =>
            array(
                'name' => 'Мексика',
                'english' => 'MEXICO',
                'numeric_code' => '484',
            ),
        'МИКРОНЕЗИЯ' =>
            array(
                'name' => 'Микронезия',
                'english' => 'MICRONESIA, FEDERATED STATES OF',
                'numeric_code' => '583',
            ),
        'МОЗАМБИК' =>
            array(
                'name' => 'Мозамбик',
                'english' => 'MOZAMBIQUE',
                'numeric_code' => '508',
            ),
        'МОЛДОВА' =>
            array(
                'name' => 'Молдова',
                'english' => 'MOLDOVA',
                'numeric_code' => '498',
            ),
        'МОНАКО' =>
            array(
                'name' => 'Монако',
                'english' => 'MONACO',
                'numeric_code' => '492',
            ),
        'МОНГОЛИЯ' =>
            array(
                'name' => 'Монголия',
                'english' => 'MONGOLIA',
                'numeric_code' => '496',
            ),
        'МОНТСЕРРАТ' =>
            array(
                'name' => 'Монтсеррат',
                'english' => 'MONTSERRAT',
                'numeric_code' => '500',
            ),
        'МЬЯНМА' =>
            array(
                'name' => 'Мьянма',
                'english' => 'BURMA',
                'numeric_code' => '104',
            ),
        'НАМИБИЯ' =>
            array(
                'name' => 'Намибия',
                'english' => 'NAMIBIA',
                'numeric_code' => '516',
            ),
        'НАУРУ' =>
            array(
                'name' => 'Науру',
                'english' => 'NAURU',
                'numeric_code' => '520',
            ),
        'НЕПАЛ' =>
            array(
                'name' => 'Непал',
                'english' => 'NEPAL',
                'numeric_code' => '524',
            ),
        'НИГЕР' =>
            array(
                'name' => 'Нигер',
                'english' => 'NIGER',
                'numeric_code' => '562',
            ),
        'НИГЕРИЯ' =>
            array(
                'name' => 'Нигерия',
                'english' => 'NIGERIA',
                'numeric_code' => '566',
            ),
        'НИДЕРЛАНДЫ' =>
            array(
                'name' => 'Нидерланды',
                'english' => 'NETHERLANDS',
                'numeric_code' => '528',
            ),
        'НИКАРАГУА' =>
            array(
                'name' => 'Никарагуа',
                'english' => 'NICARAGUA',
                'numeric_code' => '558',
            ),
        'НИУЭ' =>
            array(
                'name' => 'Ниуэ',
                'english' => 'NIUE',
                'numeric_code' => '570',
            ),
        'НОВАЯ ЗЕЛАНДИЯ' =>
            array(
                'name' => 'Новая Зеландия',
                'english' => 'NEW ZEALAND',
                'numeric_code' => '554',
            ),
        'НОВАЯ КАЛЕДОНИЯ' =>
            array(
                'name' => 'Новая Каледония',
                'english' => 'NEW CALEDONIA',
                'numeric_code' => '540',
            ),
        'НОРВЕГИЯ' =>
            array(
                'name' => 'Норвегия',
                'english' => 'NORWAY',
                'numeric_code' => '578',
            ),
        'ОБЪЕДИНЕННЫЕ АРАБСКИЕ ЭМИРАТЫ' =>
            array(
                'name' => 'Объединенные Арабские Эмираты',
                'english' => 'UNITED ARAB EMIRATES',
                'numeric_code' => '784',
            ),
        'ОМАН' =>
            array(
                'name' => 'Оман',
                'english' => 'OMAN',
                'numeric_code' => '512',
            ),
        'ОСТРОВ БУВЕ' =>
            array(
                'name' => 'Остров Буве',
                'english' => 'BOUVET ISLAND',
                'numeric_code' => '074',
            ),
        'ОСТРОВ МЭН' =>
            array(
                'name' => 'Остров Мэн',
                'english' => 'ISLE OF MAN',
                'numeric_code' => '833',
            ),
        'ОСТРОВ НОРФОЛК' =>
            array(
                'name' => 'Остров Норфолк',
                'english' => 'NORFOLK ISLAND',
                'numeric_code' => '574',
            ),
        'ОСТРОВ РОЖДЕСТВА' =>
            array(
                'name' => 'Остров Рождества',
                'english' => 'CHRISTMAS ISLAND',
                'numeric_code' => '162',
            ),
        'ОСТРОВ ХЕРД И ОСТРОВА МАКДОНАЛЬД' =>
            array(
                'name' => 'Остров Херд и острова Макдональд',
                'english' => 'HEARD ISLAND AND MCDONALD ISLANDS',
                'numeric_code' => '334',
            ),
        'ОСТРОВА КАЙМАН' =>
            array(
                'name' => 'Острова Кайман',
                'english' => 'CAYMAN ISLANDS',
                'numeric_code' => '136',
            ),
        'ОСТРОВА КУКА' =>
            array(
                'name' => 'Острова Кука',
                'english' => 'COOK ISLANDS',
                'numeric_code' => '184',
            ),
        'ОСТРОВА ТЕРКС И КАЙКОС' =>
            array(
                'name' => 'Острова Теркс и Кайкос',
                'english' => 'TURKS AND CAICOS ISLANDS',
                'numeric_code' => '796',
            ),
        'ПАКИСТАН' =>
            array(
                'name' => 'Пакистан',
                'english' => 'PAKISTAN',
                'numeric_code' => '586',
            ),
        'ПАЛАУ' =>
            array(
                'name' => 'Палау',
                'english' => 'PALAU',
                'numeric_code' => '585',
            ),
        'ПАЛЕСТИНСКАЯ ТЕРРИТОРИЯ' =>
            array(
                'name' => 'Палестинская территория',
                'english' => 'PALESTINIAN TERRITORY, OCCUPIED',
                'numeric_code' => '275',
            ),
        'ПАНАМА' =>
            array(
                'name' => 'Панама',
                'english' => 'PANAMA',
                'numeric_code' => '591',
            ),
        'ВАТИКАН' =>
            array(
                'name' => 'Ватикан',
                'english' => 'HOLY SEE (VATICAN CITY STATE)',
                'numeric_code' => '336',
            ),
        'ПАПУА-НОВАЯ ГВИНЕЯ' =>
            array(
                'name' => 'Папуа-Новая Гвинея',
                'english' => 'PAPUA NEW GUINEA',
                'numeric_code' => '598',
            ),
        'ПАРАГВАЙ' =>
            array(
                'name' => 'Парагвай',
                'english' => 'PARAGUAY',
                'numeric_code' => '600',
            ),
        'ПЕРУ' =>
            array(
                'name' => 'Перу',
                'english' => 'PERU',
                'numeric_code' => '604',
            ),
        'ПИТКЕРН' =>
            array(
                'name' => 'Питкерн',
                'english' => 'PITCAIRN',
                'numeric_code' => '612',
            ),
        'ПОЛЬША' =>
            array(
                'name' => 'Польша',
                'english' => 'POLAND',
                'numeric_code' => '616',
            ),
        'ПОРТУГАЛИЯ' =>
            array(
                'name' => 'Португалия',
                'english' => 'PORTUGAL',
                'numeric_code' => '620',
            ),
        'ПУЭРТО-РИКО' =>
            array(
                'name' => 'Пуэрто-Рико',
                'english' => 'PUERTO RICO',
                'numeric_code' => '630',
            ),
        'РЕСПУБЛИКА МАКЕДОНИЯ' =>
            array(
                'name' => 'Республика Македония',
                'english' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
                'numeric_code' => '807',
            ),
        'РЕЮНЬОН' =>
            array(
                'name' => 'Реюньон',
                'english' => 'REUNION',
                'numeric_code' => '638',
            ),
        'РУАНДА' =>
            array(
                'name' => 'Руанда',
                'english' => 'RWANDA',
                'numeric_code' => '646',
            ),
        'РУМЫНИЯ' =>
            array(
                'name' => 'Румыния',
                'english' => 'ROMANIA',
                'numeric_code' => '642',
            ),
        'САМОА' =>
            array(
                'name' => 'Самоа',
                'english' => 'SAMOA',
                'numeric_code' => '882',
            ),
        'САН-МАРИНО' =>
            array(
                'name' => 'Сан-Марино',
                'english' => 'SAN MARINO',
                'numeric_code' => '674',
            ),
        'САН-ТОМЕ И ПРИНСИПИ' =>
            array(
                'name' => 'Сан-Томе и Принсипи',
                'english' => 'SAO TOME AND PRINCIPE',
                'numeric_code' => '678',
            ),
        'САУДОВСКАЯ АРАВИЯ' =>
            array(
                'name' => 'Саудовская Аравия',
                'english' => 'SAUDI ARABIA',
                'numeric_code' => '682',
            ),
        'СВАЗИЛЕНД' =>
            array(
                'name' => 'Свазиленд',
                'english' => 'SWAZILAND',
                'numeric_code' => '748',
            ),
        'СВЯТОЙ ЕЛЕНЫ ОСТРОВ' =>
            array(
                'name' => 'Святой Елены остров',
                'english' => 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA',
                'numeric_code' => '654',
            ),
        'СЕВЕРНЫЕ МАРИАНСКИЕ ОСТРОВА' =>
            array(
                'name' => 'Северные Марианские острова',
                'english' => 'NORTHERN MARIANA ISLANDS',
                'numeric_code' => '580',
            ),
        'СЕН-БАРТЕЛЬМИ' =>
            array(
                'name' => 'Сен-Бартельми',
                'english' => 'SAINT BARTHÉLEMY',
                'numeric_code' => '652',
            ),
        'СЕН-МАРТЕН' =>
            array(
                'name' => 'Сен-Мартен',
                'english' => 'SAINT MARTIN (FRENCH PART)',
                'numeric_code' => '663',
            ),
        'СЕНЕГАЛ' =>
            array(
                'name' => 'Сенегал',
                'english' => 'SENEGAL',
                'numeric_code' => '686',
            ),
        'СЕНТ-ВИНСЕНТ И ГРЕНАДИНЫ' =>
            array(
                'name' => 'Сент-Винсент и Гренадины',
                'english' => 'SAINT VINCENT AND THE GRENADINES',
                'numeric_code' => '670',
            ),
        'СЕНТ-КИТС И НЕВИС' =>
            array(
                'name' => 'Сент-Китс и Невис',
                'english' => 'SAINT KITTS AND NEVIS',
                'numeric_code' => '659',
            ),
        'СЕНТ-ЛЮСИЯ' =>
            array(
                'name' => 'Сент-Люсия',
                'english' => 'SAINT LUCIA',
                'numeric_code' => '662',
            ),
        'СЕНТ-ПЬЕР И МИКЕЛОН' =>
            array(
                'name' => 'Сент-Пьер и Микелон',
                'english' => 'SAINT PIERRE AND MIQUELON',
                'numeric_code' => '666',
            ),
        'СЕРБИЯ' =>
            array(
                'name' => 'Сербия',
                'english' => 'SERBIA',
                'numeric_code' => '688',
            ),
        'СЕЙШЕЛЫ' =>
            array(
                'name' => 'Сейшелы',
                'english' => 'SEYCHELLES',
                'numeric_code' => '690',
            ),
        'СИНГАПУР' =>
            array(
                'name' => 'Сингапур',
                'english' => 'SINGAPORE',
                'numeric_code' => '702',
            ),
        'СИНТ-МАРТЕН' =>
            array(
                'name' => 'Синт-Мартен',
                'english' => 'SINT MAARTEN',
                'numeric_code' => '534',
            ),
        'СИРИЙСКАЯ АРАБСКАЯ РЕСПУБЛИКА' =>
            array(
                'name' => 'Сирийская Арабская Республика',
                'english' => 'SYRIAN ARAB REPUBLIC',
                'numeric_code' => '760',
            ),
        'СЛОВАКИЯ' =>
            array(
                'name' => 'Словакия',
                'english' => 'SLOVAKIA',
                'numeric_code' => '703',
            ),
        'СЛОВЕНИЯ' =>
            array(
                'name' => 'Словения',
                'english' => 'SLOVENIA',
                'numeric_code' => '705',
            ),
        'ВЕЛИКОБРИТАНИЯ' =>
            array(
                'name' => 'Великобритания',
                'english' => 'UNITED KINGDOM',
                'numeric_code' => '826',
            ),
        'СОЕДИНЕННЫЕ ШТАТЫ АМЕРИКИ' =>
            array(
                'name' => 'Соединенные Штаты Америки',
                'english' => 'UNITED STATES',
                'numeric_code' => '840',
            ),
        'СОЛОМОНОВЫ ОСТРОВА' =>
            array(
                'name' => 'Соломоновы острова',
                'english' => 'SOLOMON ISLANDS',
                'numeric_code' => '090',
            ),
        'СОМАЛИ' =>
            array(
                'name' => 'Сомали',
                'english' => 'SOMALIA',
                'numeric_code' => '706',
            ),
        'СУДАН' =>
            array(
                'name' => 'Судан',
                'english' => 'SUDAN',
                'numeric_code' => '736',
            ),
        'СУРИНАМ' =>
            array(
                'name' => 'Суринам',
                'english' => 'SURINAME',
                'numeric_code' => '740',
            ),
        'СЬЕРРА-ЛЕОНЕ' =>
            array(
                'name' => 'Сьерра-Леоне',
                'english' => 'SIERRA LEONE',
                'numeric_code' => '694',
            ),
        'ТАДЖИКИСТАН' =>
            array(
                'name' => 'Таджикистан',
                'english' => 'TAJIKISTAN',
                'numeric_code' => '762',
            ),
        'ТАИЛАНД' =>
            array(
                'name' => 'Таиланд',
                'english' => 'THAILAND',
                'numeric_code' => '764',
            ),
        'ТАЙВАНЬ' =>
            array(
                'name' => 'Тайвань',
                'english' => 'TAIWAN, PROVINCE OF CHINA',
                'numeric_code' => '158',
            ),
        'ТАНЗАНИЯ' =>
            array(
                'name' => 'Танзания',
                'english' => 'TANZANIA, UNITED REPUBLIC OF',
                'numeric_code' => '834',
            ),
        'ТИМОР-ЛЕСТЕ' =>
            array(
                'name' => 'Тимор-Лесте',
                'english' => 'TIMOR-LESTE',
                'numeric_code' => '626',
            ),
        'ТОГО' =>
            array(
                'name' => 'Того',
                'english' => 'TOGO',
                'numeric_code' => '768',
            ),
        'ТОКЕЛАУ' =>
            array(
                'name' => 'Токелау',
                'english' => 'TOKELAU',
                'numeric_code' => '772',
            ),
        'ТОНГА' =>
            array(
                'name' => 'Тонга',
                'english' => 'TONGA',
                'numeric_code' => '776',
            ),
        'ТРИНИДАД И ТОБАГО' =>
            array(
                'name' => 'Тринидад и Тобаго',
                'english' => 'TRINIDAD AND TOBAGO',
                'numeric_code' => '780',
            ),
        'ТУВАЛУ' =>
            array(
                'name' => 'Тувалу',
                'english' => 'TUVALU',
                'numeric_code' => '798',
            ),
        'ТУНИС' =>
            array(
                'name' => 'Тунис',
                'english' => 'TUNISIA',
                'numeric_code' => '788',
            ),
        'ТУРКМЕНИЯ' =>
            array(
                'name' => 'Туркмения',
                'english' => 'TURKMENISTAN',
                'numeric_code' => '795',
            ),
        'ТУРЦИЯ' =>
            array(
                'name' => 'Турция',
                'english' => 'TURKEY',
                'numeric_code' => '792',
            ),
        'УГАНДА' =>
            array(
                'name' => 'Уганда',
                'english' => 'UGANDA',
                'numeric_code' => '800',
            ),
        'УЗБЕКИСТАН' =>
            array(
                'name' => 'Узбекистан',
                'english' => 'UZBEKISTAN',
                'numeric_code' => '860',
            ),
        'УКРАИНА' =>
            array(
                'name' => 'Украина',
                'english' => 'UKRAINE',
                'numeric_code' => '804',
            ),
        'УОЛЛИС И ФУТУНА' =>
            array(
                'name' => 'Уоллис и Футуна',
                'english' => 'WALLIS AND FUTUNA',
                'numeric_code' => '876',
            ),
        'УРУГВАЙ' =>
            array(
                'name' => 'Уругвай',
                'english' => 'URUGUAY',
                'numeric_code' => '858',
            ),
        'ФАРЕРСКИЕ ОСТРОВА' =>
            array(
                'name' => 'Фарерские острова',
                'english' => 'FAROE ISLANDS',
                'numeric_code' => '234',
            ),
        'ФИДЖИ' =>
            array(
                'name' => 'Фиджи',
                'english' => 'FIJI',
                'numeric_code' => '242',
            ),
        'ФИЛИППИНЫ' =>
            array(
                'name' => 'Филиппины',
                'english' => 'PHILIPPINES',
                'numeric_code' => '608',
            ),
        'ФИНЛЯНДИЯ' =>
            array(
                'name' => 'Финляндия',
                'english' => 'FINLAND',
                'numeric_code' => '246',
            ),
        'ФОЛКЛЕНДСКИЕ ОСТРОВА' =>
            array(
                'name' => 'Фолклендские острова',
                'english' => 'FALKLAND ISLANDS (MALVINAS)',
                'numeric_code' => '238',
            ),
        'ФРАНЦИЯ' =>
            array(
                'name' => 'Франция',
                'english' => 'FRANCE',
                'numeric_code' => '250',
            ),
        'ФРАНЦУЗСКАЯ ГВИАНА' =>
            array(
                'name' => 'Французская Гвиана',
                'english' => 'FRENCH GUIANA',
                'numeric_code' => '254',
            ),
        'ФРАНЦУЗСКАЯ ПОЛИНЕЗИЯ' =>
            array(
                'name' => 'Французская Полинезия',
                'english' => 'FRENCH POLYNESIA',
                'numeric_code' => '258',
            ),
        'ФРАНЦУЗСКИЕ ЮЖНЫЕ ТЕРРИТОРИИ' =>
            array(
                'name' => 'Французские Южные территории',
                'english' => 'FRENCH SOUTHERN TERRITORIES',
                'numeric_code' => '260',
            ),
        'ХОРВАТИЯ' =>
            array(
                'name' => 'Хорватия',
                'english' => 'CROATIA',
                'numeric_code' => '191',
            ),
        'ЦЕНТРАЛЬНО-АФРИКАНСКАЯ РЕСПУБЛИКА' =>
            array(
                'name' => 'Центрально-Африканская Республика',
                'english' => 'CENTRAL AFRICAN REPUBLIC',
                'numeric_code' => '140',
            ),
        'ЧАД' =>
            array(
                'name' => 'Чад',
                'english' => 'CHAD',
                'numeric_code' => '148',
            ),
        'ЧЕРНОГОРИЯ' =>
            array(
                'name' => 'Черногория',
                'english' => 'MONTENEGRO',
                'numeric_code' => '499',
            ),
        'ЧЕШСКАЯ РЕСПУБЛИКА' =>
            array(
                'name' => 'Чешская Республика',
                'english' => 'CZECH REPUBLIC',
                'numeric_code' => '203',
            ),
        'ЧИЛИ' =>
            array(
                'name' => 'Чили',
                'english' => 'CHILE',
                'numeric_code' => '152',
            ),
        'ШВЕЙЦАРИЯ' =>
            array(
                'name' => 'Швейцария',
                'english' => 'SWITZERLAND',
                'numeric_code' => '756',
            ),
        'ШВЕЦИЯ' =>
            array(
                'name' => 'Швеция',
                'english' => 'SWEDEN',
                'numeric_code' => '752',
            ),
        'ШПИЦБЕРГЕН И ЯН МАЙЕН' =>
            array(
                'name' => 'Шпицберген и Ян Майен',
                'english' => 'SVALBARD AND JAN MAYEN',
                'numeric_code' => '744',
            ),
        'ШРИ-ЛАНКА' =>
            array(
                'name' => 'Шри-Ланка',
                'english' => 'SRI LANKA',
                'numeric_code' => '144',
            ),
        'ЭКВАДОР' =>
            array(
                'name' => 'Эквадор',
                'english' => 'ECUADOR',
                'numeric_code' => '218',
            ),
        'ЭКВАТОРИАЛЬНАЯ ГВИНЕЯ' =>
            array(
                'name' => 'Экваториальная Гвинея',
                'english' => 'EQUATORIAL GUINEA',
                'numeric_code' => '226',
            ),
        'ЭЛАНДСКИЕ ОСТРОВА' =>
            array(
                'name' => 'Эландские острова',
                'english' => 'ÅLAND ISLANDS',
                'numeric_code' => '248',
            ),
        'ЭЛЬ-САЛЬВАДОР' =>
            array(
                'name' => 'Эль-Сальвадор',
                'english' => 'EL SALVADOR',
                'numeric_code' => '222',
            ),
        'ЭРИТРЕЯ' =>
            array(
                'name' => 'Эритрея',
                'english' => 'ERITREA',
                'numeric_code' => '232',
            ),
        'ЭСТОНИЯ' =>
            array(
                'name' => 'Эстония',
                'english' => 'ESTONIA',
                'numeric_code' => '233',
            ),
        'ЭФИОПИЯ' =>
            array(
                'name' => 'Эфиопия',
                'english' => 'ETHIOPIA',
                'numeric_code' => '231',
            ),
        'ЮЖНАЯ АФРИКА' =>
            array(
                'name' => 'Южная Африка',
                'english' => 'SOUTH AFRICA',
                'numeric_code' => '710',
            ),
        'ЮЖНАЯ ДЖОРДЖИЯ И ЮЖНЫЕ САНДВИЧЕВЫ ОСТРОВА' =>
            array(
                'name' => 'Южная Джорджия и Южные Сандвичевы острова',
                'english' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
                'numeric_code' => '239',
            ),
        'ЮЖНАЯ ОСЕТИЯ' =>
            array(
                'name' => 'Южная Осетия',
                'english' => 'SOUTH OSSETIA',
                'numeric_code' => '896',
            ),
        'ЮЖНЫЙ СУДАН' =>
            array(
                'name' => 'Южный Судан',
                'english' => 'SOUTH SUDAN',
                'numeric_code' => '728',
            ),
        'ЯМАЙКА' =>
            array(
                'name' => 'Ямайка',
                'english' => 'JAMAICA',
                'numeric_code' => '388',
            ),
        'ЯПОНИЯ' =>
            array(
                'name' => 'Япония',
                'english' => 'JAPAN',
                'numeric_code' => '392',
            ),
    );

    /**
     *
     */
    public function __construct() {
        if (!self::$countries_converted && !nc_core::get_object()->NC_UNICODE) {
            self::$countries = nc_core::get_object()->utf8->array_utf2win(self::$countries);
            self::$countries_converted = true;
        }
    }


    /**
     * @param string $original_search_string
     * @return nc_netshop_location_data_collection
     */
    public function find_locations($original_search_string) {
        // Предполагается, что адрес будет содержать запятую после названия страны («Великобритания, Llanfairpwllgwyngyll»)
        list($string_before_comma) = explode(',', "$original_search_string,", 2);
        $search = nc_strtoupper(trim($string_before_comma));

        // Полное совпадение по названию
        if (isset(self::$countries[$search])) {
            return $this->make_collection_with_single_match(self::$countries[$search], $original_search_string);
        }

        $locations = new nc_netshop_location_data_collection();
        foreach (self::$countries as $uppercase_country_name => $country) {
            // Полное совпадение по английскому названию
            // (совпадение по «полному названию» (например: «Королевство Нидерландов») убрано из-за
            // того, что совпадение по «невидимому» названию даёт неочевидный результат)
            if ($country['english'] === $search) {
                return $this->make_collection_with_single_match($country, $original_search_string);
            }

            $weight =
                $this->get_match_weight($uppercase_country_name, $search, self::WEIGHT_NAME_STARTS_WITH) ?:
                $this->get_match_weight($country['english'], $search, self::WEIGHT_OTHER_NAME_STARTS_WITH);

            if ($weight !== 0) {
                $locations->add($this->make_data_object($country, $weight));
            }
        }

        return $locations->sort_by_property_value('weight', SORT_NUMERIC, true);
    }

    /**
     * @param $haystack
     * @param $needle
     * @param $weight_starts
     * @param int $weight_contains
     * @return int
     */
    protected function get_match_weight($haystack, $needle, $weight_starts, $weight_contains = self::WEIGHT_DEFAULT) {
        $position = strpos($haystack, $needle);
        if ($position === false) {
            return 0;
        }
        if ($position === 0) {
            return $weight_starts;
        }
        return $weight_contains;
    }

    /**
     * @param array $country
     * @param int $weight
     * @return nc_netshop_location_data
     */
    protected function make_data_object(array $country, $weight) {
        return new nc_netshop_location_data(array(
            'country_name' => $country['name'],
            'country_numeric_code' => $country['numeric_code'],
            'name' => $country['name'],
            'is_suffix_allowed' => true,
            'weight' => $weight,
        ));
    }

    /**
     * @param array $country
     * @param string $location_string
     * @return nc_netshop_location_data_collection
     */
    protected function make_collection_with_single_match(array $country, $location_string) {
        $data_object = $this->make_data_object($country, self::WEIGHT_EXACT_NAME);
        $data_object->set('is_exact_match', true);

        // Считаем всё, что после первой запятой, названием населённого пункта
        list(, $string_after_comma) = explode(',', "$location_string,", 2);
        $string_after_comma = trim($string_after_comma);
        $string_after_comma = trim($string_after_comma, ',');
        if ($string_after_comma) {
            $data_object->set('name', "$country[name], $string_after_comma");
            $data_object->set('locality_name', $string_after_comma);
        }

        return new nc_netshop_location_data_collection(array($data_object));
    }

}