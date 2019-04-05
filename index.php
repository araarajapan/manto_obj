<?php

ini_set('log_errors', 'on');  //ログを取るか
ini_set('error_log', 'php.log');  //ログの出力ファイルを指定
session_start(); //セッション使う

// 冗長なディレクトリを定数化
const URL = 'img/';

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

  public function __construct($name, $sex, $hp, $mp, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->sex = $sex;
    $this->hp = $hp;
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
    $magicPoint = 1;
    $healPoint = mt_rand(Human::HEALMIN, Human::HEALMAX);
    if (($this->getHp() + $healPoint) > $this->maxHp) {
      $healPoint = $healPoint - (($this->getHp() + $healPoint) - $this->maxHp);
    }

    $this->setHp($this->getHp() + $healPoint);
    $this->mp -= $magicPoint;
    History::set($healPoint . 'ポイントの回復！');
  }
}
// モンスタークラス
class Monster extends Creature
{
  // プロパティ
  protected $img;
  // コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  // ゲッター
  public function getImg()
  {
    return $this->img;
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

class God
{
  protected $name;
  protected $img;

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
//Human($name, $sex, $hp, $mp, $attackMin, $attackMax)
//God($name, $img)
//Monster($name, $hp, $img, $attackMin, $attackMax)
//MagicMonster($name, $hp, $img, $attackMin, $attackMax, $magicAttack)
$human = new Human('勇者見習い', Sex::MAN, 500, 3, 40, 120);
$god = new God('神様', URL . 'god.png');
$monsters[] = new Monster('フランケン', 100, URL . 'monster01.png', 20, 40);
$monsters[] = new MagicMonster('フランケンNEO', 300, URL . 'monster02.png', 20, 60, mt_rand(50, 100));
$monsters[] = new Monster('ドラキュリー', 200, URL . 'monster03.png', 30, 50);
$monsters[] = new MagicMonster('ドラキュラ男爵', 400, URL . 'monster04.png', 50, 80, mt_rand(60, 120));
$monsters[] = new Monster('スカルフェイス', 150, URL . 'monster05.png', 30, 60);
$monsters[] = new Monster('毒ハンド', 100, URL . 'monster06.png', 10, 30);
$monsters[] = new Monster('泥ハンド', 120, URL . 'monster07.png', 20, 30);
$monsters[] = new Monster('血のハンド', 180, URL . 'monster08.png', 30, 50);
$monsters[] = new FlyingMonster('見習い魔女', 260, URL . 'monster09.png', 20, 70);

function createMonster()
{
  global $monsters;
  $monster =  $monsters[mt_rand(0, 8)];
  History::set($monster->getName() . 'が現れた！');
  $_SESSION['monster'] =  $monster;
  unset($_SESSION['god']);
}
function createHuman()
{
  global $human;
  $_SESSION['human'] =  $human;
}
function createGod()
{
  global $god;
  History::set('あなたを手助けしてくれる' . $god->getName() . 'が現れた！');
  $_SESSION['god'] =  $god;
}

function decideCreate() //モンスターか神様を生成させる
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
  createHuman();
  createMonster();
}
function gameOver()
{
  $_SESSION = array();
}


//1.post送信されていた場合
if (!empty($_POST)) {
  $startFlg = (!empty($_POST['start'])) ? true : false;

  $attackFlg = (!empty($_POST['attack'])) ? true : false;
  $healFlg = (!empty($_POST['heal'])) ? true : false;

  $recoveryFlg = (!empty($_POST['recovery'])) ? true : false;
  $powerFlg = (!empty($_POST['power'])) ? true : false;
  $toughFlg = (!empty($_POST['tough'])) ? true : false;

  error_log(' POSTされた！ ');

  if ($startFlg) {
    History::set(' ゲームスタート！ ');
    init();
  } elseif (empty($_SESSION['god'])) {
    //モンスターの場合の処理

    //攻撃・回復・逃げるで処理を分岐させる
    // 攻撃するを押した場合
    if ($attackFlg) {

      // モンスターに攻撃を与える
      History::set($_SESSION['human']->getName() . 'の攻撃！');
      $_SESSION['human']->attack($_SESSION['monster']);
      $_SESSION['monster']->sayCry();

      // モンスターが攻撃をする
      History::set($_SESSION['monster']->getName() . 'の攻撃！');
      $_SESSION['monster']->attack($_SESSION['human']);
      $_SESSION['human']->sayCry();

      // 自分のhpが0以下になったらゲームオーバー
      if ($_SESSION['human']->getHp() <= 0) {
        gameOver();
      } else {
        // hpが0以下になったら、別のモンスターを出現させる
        if ($_SESSION['monster']->getHp() <= 0) {
          History::set($_SESSION['monster']->getName() . 'を倒した！');
          decideCreate();
          $_SESSION['knockDownCount'] = $_SESSION['knockDownCount'] + 1;
        }
      }
    } elseif ($healFlg) {

      // 自分を回復させる
      History::set($_SESSION['human']->getName() . 'のHPを回復！');
      $_SESSION['human']->heal();
      $_SESSION['human']->sayMsg();

      // モンスターが攻撃をする
      History::set($_SESSION['monster']->getName() . 'の攻撃！');
      $_SESSION['monster']->attack($_SESSION['human']);
      $_SESSION['human']->sayCry();

      // 自分のhpが0以下になったらゲームオーバー
      if ($_SESSION['human']->getHp() <= 0) {
        gameOver();
      }
    } else { //逃げるを押した場合
      History::set('逃げた！');
      decideCreate();
    }
  } else {
    //神様の場合の処理
    if ($recoveryFlg) {
      // 勇者のHPを最大値まで戻す
      History::set($_SESSION['human']->getName() . 'の体力が回復していく！');
      $_SESSION['god']->recovery($_SESSION['human']);
    } elseif ($powerFlg) {
      // 勇者の攻撃力を強化
      History::set($_SESSION['human']->getName() . 'の攻撃力が上がっていく！');
      $_SESSION['god']->power($_SESSION['human']);
    } elseif ($toughFlg) {
      // 勇者のHPを増強
      History::set($_SESSION['human']->getName() . 'の体力が上がっていく！');
      $_SESSION['god']->tough($_SESSION['human']);
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
      width: 25%;
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
      <h2 style="margin-top:60px;">GAME START ?</h2>
      <form method="post">
        <input type="submit" name="start" value="▶ゲームスタート">
      </form>
    <?php
  } elseif (empty($_SESSION['god'])) { ?>
      <!-- オブジェクトがモンスターだった場合の表示 -->
      <h2><?php echo $_SESSION['monster']->getName() . ' が現れた !!'; ?></h2>
      <div style="height: 150px;">
        <img src="<?php echo $_SESSION['monster']->getImg(); ?>" style="width:120px; height:auto; margin:40px auto 0 auto; display:block;">
      </div>
      <p style="font-size:14px; text-align:center;">モンスターのHP：<?php echo $_SESSION['monster']->getHp(); ?></p>
      <p>倒したモンスター数：<?php echo $_SESSION['knockDownCount']; ?></p>
      <p>勇者の残りHP：<?php echo $_SESSION['human']->getHp(); ?></p>
      <p>勇者の残りMP：<?php echo $_SESSION['human']->getMp(); ?></p>
      <form method="post">
        <input type="submit" name="attack" value="▶攻撃する">
        <?php if ($_SESSION['human']->getMp() >= 1) { ?>
          <input type="submit" name="heal" value="▶回復する(mp:1)">
        <?php
      } else { ?>
          <input type="submit" name="heal" value="▶回復する(mp不足)" class="btn-short" disabled>
        <?php
      } ?>
        <input type="submit" name="escape" value="▶逃げる">
        <input type="submit" name="start" value="▶ゲームリスタート">
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
      <p>勇者の残りHP：<?php echo $_SESSION['human']->getHp(); ?></p>
      <p>勇者の残りMP：<?php echo $_SESSION['human']->getMp(); ?></p>
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