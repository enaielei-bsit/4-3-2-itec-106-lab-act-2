<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono&family=DM+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <title>BSIT 4-3 | ITEC 106 | Laboratory Activity 2 | PHP Form Handling</title>
</head>
<body>
    <div id="main-content">
        <?php
            define("LETTERS", "ABCDEFGHIJKLMNOPQRSTUVWXYZ");

            function pop($array, $index=0) {
                return array_splice($array, $index, 1);
            }

            function get($array, $key, $default=null) {
                return array_key_exists($key, $array) ? $array[$key] : $default;
            }

            function readJson($path) {
                $string = file_get_contents($path);
                return json_decode($string, true);
            }

            function pprint($obj) {
                var_dump($obj);
            }

            class Question {
                public $content;
                public $choices;
                public $answers;
                public $all;
                public $ordered;

                public function __construct(
                    $content, $choices, $answers, $all=false, $ordered=false) {
                    $this->content = $content;
                    $this->choices = $choices;
                    $this->answers = $answers;
                    $this->all = $all;
                    $this->ordered = $ordered;
                }

                public function check(...$answers) {
                    $result = true;
                    if($this->all) {
                        $result = $result && ($this->answers == $answers);
                    } else {
                        $result = !empty(array_intersect(
                            $this->answers, $answers));
                    }
                    if($this->ordered) {
                        $result = $result && ($this->answers === $answers);
                    }

                    return $result;
                }

                public function getAnswers() {
                    $answers = [];
                    foreach($this->answers as $index) { 
                        array_push($answers, $this->choices[$index]);
                    }

                    return $answers;
                }

                public static function fromAssocArray($array) {
                    $content = get($array, "content", "");
                    $choices = get($array, "choices", []);
                    $answers = get($array, "answers", []);
                    $all = get($array, "all", false);
                    $ordered = get($array, "ordered", false);

                    return new Question(
                        $content, $choices, $answers, $all, $ordered);
                }
            }
            
            define("QUESTIONS_PATH", "./questions.json");
            $questions = array_map(
                fn($x) => Question::fromAssocArray($x), readJson(QUESTIONS_PATH));
            $submitted = !empty($_REQUEST["submit"]);

            if($submitted) {
                $answers = [];
                foreach($questions as $index => $_) array_push($answers, []);

                $score = 0;
                $total = count($questions);
                $rawAnswers = $_REQUEST["answers"] ?? [];
                foreach($answers as $index => $answer) {
                    $sindex = $index . "";
                    if(array_key_exists($sindex, $rawAnswers))
                        array_push($answers[$index], (int) $rawAnswers[$sindex]);
                
                    $score += $questions[$index]->check(...$answers[$index]) ? 1 : 0;
                }
            }
        ?>

        <?php if($submitted) { ?>
            <h2>Legend</h2>
            <div class="legend">
                <div class="chosen">Chosen Answer</div>
                <div class="target">Correct Answer</div>
                <div class="correct">Answered Correctly</div>
                <div class="wrong">Answered Wrongly</div>
            </div>
            <br>
            <h2>Score</h2>
            <div class="score">
                <div class="bar" style="width: <?= round(($score / (float) $total) * 100, 2) ?>%;"></div>
                <span class="value"><?= $score ?></span> / <span class="target"><?= $total ?></span>
            </div>
            <br>
            <br>
            <br>
        <?php } ?>

        <form action="#" method="get">
            <?php foreach($questions as $index => $question) { ?>
                <div class="question">
                    <div class="content"><b><?= $index + 1 ?>.)</b> <?= $question->content ?></div>
                    <br>
                    <div class="choices">
                        <?php foreach($question->choices as $cindex => $choice) {
                            $cid = "q" . $index . "-" . "c" . $cindex;
                            $iattr = "";
                            $cclass = "";
                            $letter = $cindex < strlen(LETTERS) ? LETTERS[$cindex] . ". " : "";

                            if($submitted) {
                                $checked = in_array($cindex, $answers[$index]);
                                $target = $question->check($cindex);

                                if($checked) $iattr .= " checked";
                                if($target) $cclass .= " target";

                                if($checked && $target) {
                                    $cclass .= " correct";
                                } elseif($checked && !$target) {
                                    $cclass .= " wrong";
                                }
                            }
                        ?>
                            <div class="choice <?= $cclass ?>">
                                <input id="<?= $cid ?>" type="radio" name="answers[<?= $index ?>]" value="<?= $cindex ?>" <?= $iattr ?>>
                                <label for="<?= $cid ?>"><?= $letter . $choice ?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <br>
            <?php } ?>
            <input type="submit" name="submit" value="Submit">
            <br>
            <div class="controls">
                <a class="clear" href="./">Clear</a>
            </div>
        </form>
    </div>
</body>
</html>