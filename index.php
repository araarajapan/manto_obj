<?php

ini_set('log_errors', 'on');  //ログを取るか
ini_set('error_log', 'php.log');  //ログの出力ファイルを指定
session_start(); //セッション使う

// 冗長なディレクトリを定数化
const DIR_IMAGES = 'img/';

// モンスター達格納用
$monsters = array();

// 性別クラス
class Sex
{
  const MAN = 1;
  const WOMAN = 2;
  const OKAMA = 3;
}
// 抽象クラス（生き物クラス）
abstract class Creature
{
  protected $name;
  protected $hp;
  protected $img;
  protected $attackMin;
  protected $attackMax;
  abstract public function sayCry();
  public function setName($str)
  {
    $this->name = $str;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setHp($num)
  {
    $this->hp = $num;
  }
  public function getHp()
  {
    return $this->hp;
  }
  public function getImg()
  {
    return $this->img;
  }
  public function attack($targetObj)
  {
    $attackPoint = mt_rand($this->attackMin, $this->attackMax);
    if (!mt_rand(0, 9)) { //10分の1の確率でクリティカル
      $attackPoint = $attackPoint * 1.5;
      $attackPoint = (int)$attackPoint;
      History::set($this->getName() . 'のクリティカルヒット!!');
    }
    $targetObj->setHp($targetObj->getHp() - $attackPoint);
    History::set($attackPoint . 'ポイントのダメージ！');
  }
}
// 人クラス
class Human extends Creature
{
  //回復のパラメータをクラス定数としてセット
  const HEALMIN = 10;
  const HEALMAX = 100;

  protected $sex;
  protected $mp;
  protected $maxHp;

  public function __construct($name, $sex, $hp, $img, $mp, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->sex = $sex;
    $this->hp = $hp;
    $this->img = $img;
    $this->maxHp = $hp;
    $this->mp = $mp;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  public function setSex($num)
  {
    $this->sex = $num;
  }
  public function getSex()
  {
    return $this->sex;
  }
  public function getMp()
  {
    return $this->mp;
  }
  public function getAttackMin()
  {
    return $this->attackMin;
  }
  public function getAttackMax()
  {
    return $this->attackMax;
  }
  public function setAttack($num1, $num2)
  {
    $this->attackMin = $num1;
    $this->attackMax = $num2;
  }
  public function setMaxHp($num)
  {
    $this->maxHp = $num;
  }
  public function getMaxHp()
  {
    return $this->maxHp;
  }
  public function sayCry()
  {
    History::set($this->name . 'が叫ぶ！');
    switch ($this->sex) {
      case Sex::MAN:
        History::set('ぐはぁっ！');
        break;
      case Sex::WOMAN:
        History::set('きゃっ！');
        break;
      case Sex::OKAMA:
        History::set('もっと！♡');
        break;
    }
  }
  public function sayMsg()
  {
    switch ($this->sex) {
      case Sex::MAN:
        History::set('(' . $this->getName() . ')...力がみなぎってきた！');
        break;
      case Sex::WOMAN:
        History::set('(' . $this->getName() . ')...まだまだこれからだわ！');
        break;
      case Sex::OKAMA:
        History::set('(' . $this->getName() . ')///きくぅ♡！');
        break;
    }
  }

  public function heal()
  {
    $magicPoint = 10;
    $healPoint = mt_rand(Human::HEALMIN, Human::HEALMAX);
    if (($this->getHp() + $healPoint) > $this->maxHp) {
      $healPoint = $healPoint - (($this->getHp() + $healPoint) - $this->maxHp);
    }

    $this->setHp($this->getHp() + $healPoint);
    $this->mp -= $magicPoint;
    History::set($healPoint . 'ポイントの回復！');
  }
}
// 魔法使いクラス
class Witch extends Human
{
  public function attack($targetObj)
  {
    $magicPoint = 10;
    if ($this->mp >= $magicPoint) {
      History::set('魔法攻撃!');
      $attackPoint = mt_rand($this->attackMin, $this->attackMax) * mt_rand(0.5, 2);
      $this->mp -= $magicPoint;
      if (get_class($targetObj) == 'FlyingMonster') {
        History::set('効果が抜群!');
        $attackPoint *= 1.5;
      }
      $targetObj->setHp($targetObj->getHp() - $attackPoint);
      History::set($attackPoint . 'ポイントのダメージ！');
    } else {
      History::set('MPが無いので通常攻撃!');
      parent::attack($targetObj);
    }
  }
}
// モンスタークラス
class Monster extends Creature
{
  // コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  public function sayCry()
  {
    History::set($this->name . 'が叫ぶ！');
    History::set('はうっ！');
  }
}
// 魔法を使えるモンスタークラス
class MagicMonster extends Monster
{
  private $magicAttack;
  function __construct($name, $hp, $img, $attackMin, $attackMax, $magicAttack)
  {
    parent::__construct($name, $hp, $img, $attackMin, $attackMax);
    $this->magicAttack = $magicAttack;
  }
  public function getMagicAttack()
  {
    return $this->magicAttack;
  }
  public function attack($targetObj)
  {
    if (!mt_rand(0, 4)) { //5分の1の確率で魔法攻撃
      History::set($this->name . 'の魔法攻撃!!');
      $targetObj->setHp($targetObj->getHp() - $this->magicAttack);
      History::set($this->magicAttack . 'ポイントのダメージを受けた！');
    } else {
      parent::attack($targetObj);
    }
  }
}

class FlyingMonster extends Monster
{
  public function attack($targetObj)
  {
    if (!mt_rand(0, 2)) { //3分の1の確率で空を飛ぶ攻撃

      //空を飛ぶ攻撃の場合、パラメータを1.2倍
      $attackPoint = mt_rand($this->attackMin, $this->attackMax) * 1.2;

      //空を飛ぶ攻撃は自爆ダメージあり
      $reactionPoint = 20;

      History::set($this->name . 'の空からの体当たり攻撃!!');
      $targetObj->setHp($targetObj->getHp() - $attackPoint);
      History::set($attackPoint . 'ポイントのダメージ！');
      $this->hp -= $reactionPoint;
      History::set('体当たりの反動で' . $this->name . 'にも' . $reactionPoint . 'ポイントのダメージ！');
    } else {
      parent::attack($targetObj);
    }
  }
}

class Boss extends Monster
{
  function __construct($name, $img)
  {
    //Boss固有のHPを設定
    if (!empty($_SESSION['knockDownCount'])) {
      $bossHp = 500 + ($_SESSION['knockDownCount'] * 10);
    }
    //Boss固有の攻撃力を設定
    $bossAttackMin = 50;
    $bossAttackMax = 80;

    //その他のパラメータは親クラスと同様に初期化する
    parent::__construct($name, $bossHp, $img, $bossAttackMin, $bossAttackMax);
  }

  public function sayCry()
  {
    History::set($this->name . 'が叫ぶ！');
    History::set('そんな攻撃ではくらわんぞっ！');
  }
}

class God
{
  private $name;
  private $img;

  public function  __construct($name, $img)
  {
    $this->name = $name;
    $this->img = $img;
  }
  public function getName()
  {
    return $this->name;
  }
  public function getImg()
  {
    return $this->img;
  }
  public function recovery($targetObj)
  {

    $targetObj->setHp($targetObj->getMaxHp());

    History::set($targetObj->getName() . 'の体力が全回復した');
  }
  public function power($targetObj)
  {
    $targetObj->setAttack($targetObj->getAttackMin() + 20, $targetObj->getAttackMax() + 20);
    History::set($targetObj->getName() . 'の攻撃力がUPした');
  }
  public function tough($targetObj)
  {
    $targetObj->setMaxHp($targetObj->getMaxHp() * 2);
    History::set($targetObj->getName() . 'の体力が倍増した');
  }
}

interface HistoryInterface
{
  public static function set($str);
  public static function clear();
}
// 履歴管理クラス（インスタンス化して複数に増殖させる必要性がないクラスなので、staticにする）
class History implements HistoryInterface
{
  public static function set($str)
  {
    // セッションhistoryが作られてなければ作る
    if (empty($_SESSION['history'])) $_SESSION['history'] = '';
    // 文字列をセッションhistoryへ格納
    $_SESSION['history'] .= $str . '<br>';
  }
  public static function clear()
  {
    unset($_SESSION['history']);
  }
}

// インスタンス生成
//Human($name, $sex, $hp, $img, $mp, $attackMin, $attackMax)
//God($name, $img)
//Boss($name, $hp, $img,$attackMin, $attackMax)
//Monster($name, $hp, $img, $attackMin, $attackMax)
//MagicMonster($name, $hp, $img, $attackMin, $attackMax, $magicAttack)
$human = new Human('勇者', Sex::MAN, 500, DIR_IMAGES . 'hero.png', 30, 40, 120);
$witch = new Witch('魔法使い', Sex::WOMAN, 300, DIR_IMAGES . 'witch.png', mt_rand(50, 100), 40, 120);
$god = new God('神様', DIR_IMAGES . 'god.png');
$boss = new Boss('魔王', DIR_IMAGES . 'boss.png');
$monsters[] = new Monster('フランケン', 100, DIR_IMAGES . 'monster01.png', 20, 40);
$monsters[] = new MagicMonster('フランケンNEO', 300, DIR_IMAGES . 'monster02.png', 20, 60, mt_rand(50, 100));
$monsters[] = new Monster('ドラキュリー', 200, DIR_IMAGES . 'monster03.png', 30, 50);
$monsters[] = new MagicMonster('ドラキュラ男爵', 400, DIR_IMAGES . 'monster04.png', 50, 80, mt_rand(60, 120));
$monsters[] = new Monster('スカルフェイス', 150, DIR_IMAGES . 'monster05.png', 30, 60);
$monsters[] = new Monster('毒ハンド', 100, DIR_IMAGES . 'monster06.png', 10, 30);
$monsters[] = new Monster('泥ハンド', 120, DIR_IMAGES . 'monster07.png', 20, 30);
$monsters[] = new Monster('血のハンド', 180, DIR_IMAGES . 'monster08.png', 30, 50);
$monsters[] = new FlyingMonster('見習い魔女', 260, DIR_IMAGES . 'monster09.png', 20, 70);

function createMonster()
{
  unset($_SESSION['god']); //gotオブジェクトを削除しておく
  if ($_SESSION['knockDownCount'] >= 4 && !mt_rand(0, 3)) { //4体倒している かつ 3分の1の確率でBOSSをランダムで生成させる
    createBoss();
  } else {
    global $monsters;
    $monster =  $monsters[mt_rand(0, 8)];
    History::set($monster->getName() . 'が現れた！');
    $_SESSION['enemy'] =  $monster;
  }
}
function createHuman()
{
  global $human;
  $_SESSION['mainChara'] =  $human;
}
function createWitch()
{
  global $witch;
  $_SESSION['mainChara'] =  $witch;
}
function createGod()
{
  global $god;
  History::set('あなたを手助けしてくれる' . $god->getName() . 'が現れた！');
  $_SESSION['god'] =  $god;
}

//BOSSのHPパラメータ作成
function createBoss()
{
  //Todo:Bossのインスタンスはこのタイミングで生成する
  global $boss;
  History::set('ラスボスの' . $boss->getName() . 'が現れた！');
  $_SESSION['enemy'] =  $boss;
}

function decideCharacter()
{
  if ($_POST['chara'] == 'hero') {
    History::set('勇者が選ばれました');
    createHuman();
  } else {
    History::set('魔法使いが選ばれました');
    createWitch();
  }
}

function decideEnemy() //モンスターか神様を生成させる
{
  if (!mt_rand(0, 10)) { //10分の1の確率で神様を出現させる
    createGod();
  } else {
    createMonster();
  }
}

function init()
{
  History::clear();
  History::set('初期化します！');
  $_SESSION['knockDownCount'] = 0;
  decideCharacter();
  createMonster();
}
function gameOver()
{
  $_SESSION = array();
  $_POST = array();
}


//1.post送信されていた場合
if (!empty($_POST)) {
  $startFlg = (!empty($_POST['start'])) ? true : false;
  $reStartFlg = (!empty($_POST['reStart'])) ? true : false;

  $attackFlg = (!empty($_POST['attack'])) ? true : false;
  $healFlg = (!empty($_POST['heal'])) ? true : false;

  $recoveryFlg = (!empty($_POST['recovery'])) ? true : false;
  $powerFlg = (!empty($_POST['power'])) ? true : false;
  $toughFlg = (!empty($_POST['tough'])) ? true : false;

  error_log(' POSTされた！ ');

  if ($startFlg) {
    History::set(' ゲームスタート！ ');
    init();
  } elseif ($reStartFlg) {
    History::set(' キャラクターの選択画面に移ります ');
    gameOver();
  } elseif (empty($_SESSION['god'])) {
    //モンスターの場合の処理

    //攻撃・回復・逃げるで処理を分岐させる
    // 攻撃するを押した場合
    if ($attackFlg) {

      // モンスターに攻撃を与える
      History::set($_SESSION['mainChara']->getName() . 'の攻撃！');
      $_SESSION['mainChara']->attack($_SESSION['enemy']);
      $_SESSION['enemy']->sayCry();

      // モンスターが攻撃をする
      History::set($_SESSION['enemy']->getName() . 'の攻撃！');
      $_SESSION['enemy']->attack($_SESSION['mainChara']);
      $_SESSION['mainChara']->sayCry();

      // 自分のhpが0以下になったらゲームオーバー
      if ($_SESSION['mainChara']->getHp() <= 0) {
        gameOver();
      } else {
        // hpが0以下になったら、別のモンスターを出現させる
        if ($_SESSION['enemy']->getHp() <= 0) {
          //enemyがBossクラスだった場合→ゲーム終了へ
          if (get_class($_SESSION['enemy']) == 'Boss') {
            History::set($_SESSION['enemy']->getName() . 'を倒した！<br>ゲームクリア!');
            $_SESSION['clear_flg'] = true;

            //enemyがMonsterクラスだった場合→次の戦闘へ  
          } else {
            History::set($_SESSION['enemy']->getName() . 'を倒した！');
            $_SESSION['knockDownCount'] = $_SESSION['knockDownCount'] + 1;
            decideEnemy();
          }
        }
      }
    } elseif ($healFlg) {

      // 自分を回復させる
      History::set($_SESSION['mainChara']->getName() . 'のHPを回復！');
      $_SESSION['mainChara']->heal();
      $_SESSION['mainChara']->sayMsg();

      // モンスターが攻撃をする
      History::set($_SESSION['enemy']->getName() . 'の攻撃！');
      $_SESSION['enemy']->attack($_SESSION['mainChara']);
      $_SESSION['mainChara']->sayCry();

      // 自分のhpが0以下になったらゲームオーバー
      if ($_SESSION['mainChara']->getHp() <= 0) {
        gameOver();
      }
    } else { //逃げるを押した場合
      History::set('逃げた！');
      decideEnemy();
    }
  } else {
    //神様の場合の処理
    if ($recoveryFlg) {
      // 勇者のHPを最大値まで戻す
      History::set($_SESSION['mainChara']->getName() . 'の体力が回復していく！');
      $_SESSION['god']->recovery($_SESSION['mainChara']);
    } elseif ($powerFlg) {
      // 勇者の攻撃力を強化
      History::set($_SESSION['mainChara']->getName() . 'の攻撃力が上がっていく！');
      $_SESSION['god']->power($_SESSION['mainChara']);
    } elseif ($toughFlg) {
      // 勇者のHPを増強
      History::set($_SESSION['mainChara']->getName() . 'の体力が上がっていく！');
      $_SESSION['god']->tough($_SESSION['mainChara']);
    }
    // 神様の出現した後は必ずモンスターを出す
    createMonster();
  }
  $_POST = array();
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>ホームページのタイトル</title>
  <style>
    body {
      margin: 0 auto;
      padding: 150px;
      width: 50%;
      background: #fbfbfa;
      color: white;
    }

    h1 {
      color: white;
      font-size: 20px;
      text-align: center;
    }

    h2 {
      color: white;
      font-size: 16px;
      text-align: center;
    }

    form {
      overflow: hidden;
    }

    label img {
      margin: 3px;
      padding: 8px;
    }

    [type="radio"]:checked+label img {
      background: #FFF100;
    }

    input[type="text"] {
      color: #545454;
      height: 60px;
      width: 100%;
      padding: 5px 10px;
      font-size: 16px;
      display: block;
      margin-bottom: 10px;
      box-sizing: border-box;
    }

    input[type="password"] {
      color: #545454;
      height: 60px;
      width: 100%;
      padding: 5px 10px;
      font-size: 16px;
      display: block;
      margin-bottom: 10px;
      box-sizing: border-box;
    }

    input[type="submit"] {
      border: none;
      padding: 15px 15px;
      margin-bottom: 15px;
      background: black;
      color: white;
      float: left;
    }

    input[type="submit"]:hover {
      background: #3d3938;
      cursor: pointer;
    }

    input.btn-short {
      color: gray;
    }

    input.btn-short:hover {
      color: gray;
      cursor: not-allowed;
    }

    a {
      color: #545454;
      display: block;
    }

    a:hover {
      text-decoration: none;
    }
  </style>
</head>

<body>
  <h1 style="text-align:center; color:#333;">ゲーム「ドラ◯エ!!」</h1>
  <div style="background:black; padding:15px; position:relative;">
    <?php if (empty($_SESSION)) { ?>
      <h2 style="margin-top:60px;">Select Character</h2>
      <form method="post" style="text-align:center;">
        <input type="radio" id="hero" name="chara" value="hero" style="display:none;" checked>
        <label for="hero">
          <img src="<?php echo $human->getImg(); ?>" alt="" style="width:110px; height:150px; border:1px solid #ffffff; display:inline-block;">
        </label>
        <input type="radio" id="witch" name="chara" value="witch" style="display:none;">
        <label for="witch">
          <img src="<?php echo $witch->getImg(); ?>" alt="" style="width:110px; height:150px; border:1px solid #ffffff; display:inline-block;">
        </label>
        <input type="submit" name="start" value="▶ゲームスタート">
      </form>
    <?php
  } elseif (!empty($_SESSION['clear_flg'])) { ?>
      <h2 style="margin-top:60px;">Game cleared</h2>
      <form method="post">
        <input type="submit" name="reStart" value="▶ゲームリスタート">
      </form>
      <!-- todo clear_flgでゲームクリア画面を作る -->
    <?php
  } elseif (empty($_SESSION['god'])) { ?>
      <!-- オブジェクトがモンスターorBossだった場合の表示 -->
      <h2><?php echo $_SESSION['enemy']->getName() . ' が現れた !!'; ?></h2>
      <div style="height: 150px;">
        <img src="<?php echo $_SESSION['enemy']->getImg(); ?>" style="width:120px; height:auto; margin:40px auto 0 auto; display:block;">
      </div>
      <p style="font-size:14px; text-align:center;">モンスターのHP：<?php echo $_SESSION['enemy']->getHp(); ?></p>
      <p>倒したモンスター数：<?php echo $_SESSION['knockDownCount']; ?></p>
      <p><?php echo $_SESSION['mainChara']->getName() ?>の残りHP：<?php echo $_SESSION['mainChara']->getHp(); ?></p>
      <p><?php echo $_SESSION['mainChara']->getName() ?>の残りMP：<?php echo $_SESSION['mainChara']->getMp(); ?></p>
      <form method="post">
        <input type="submit" name="attack" value="▶攻撃する">
        <?php if ($_SESSION['mainChara']->getMp() >= 1) { ?>
          <input type="submit" name="heal" value="▶回復する(mp:10)">
        <?php
      } else { ?>
          <input type="submit" name="heal" value="▶回復する(mp不足)" class="btn-short" disabled>
        <?php
      } ?>
        <input type="submit" name="escape" value="▶逃げる">
        <input type="submit" name="reStart" value="▶ゲームリスタート">
      </form>
    <?php
  } else { ?>
      <!-- オブジェクトが神様だった場合の表示 -->
      <h2><?php echo $_SESSION['god']->getName() . 'が現れた!!'; ?></h2>
      <div style="height: 150px;">
        <img src="<?php echo $_SESSION['god']->getImg(); ?>" style="width:120px; height:auto; margin:40px auto 0 auto; display:block;">
      </div>
      <p style="font-size:14px; text-align:center;">☆☆☆3つの願いが叶います☆☆☆</p>
      <p>倒したモンスター数：<?php echo $_SESSION['knockDownCount']; ?></p>
      <p><?php echo $_SESSION['mainChara']->getName() ?>の残りHP：<?php echo $_SESSION['mainChara']->getHp(); ?></p>
      <p><?php echo $_SESSION['mainChara']->getName() ?>の残りMP：<?php echo $_SESSION['mainChara']->getMp(); ?></p>
      <form method="post">
        <input type="submit" name="recovery" value="▶回復してもらう">
        <input type="submit" name="power" value="▶強くしてもらう">
        <input type="submit" name="tough" value="▶丈夫にしてもらう">
      </form>
    <?php
  } ?>
    <div style="position:absolute; right:-350px; top:0; color:black; width:  300px ; ">
      <p><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
    </div>
  </div>
</body>

</html>