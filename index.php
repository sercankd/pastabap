<?php
 setlocale(LC_TIME, "tr_TR");
/**
 *  Pasthis - Stupid Simple Pastebin
 *
 * Copyright (C) 2014 - 2018 Julien (jvoisin) Voisin - dustri.org
 * Copyright (C) 2014 - 2018 Antoine Tenart <antoine.tenart@ack.tf>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

final class Pasthis {
    public $title;
    private $contents = array();
    private $db;

    function __construct($title = 'ABAP Paylaş') {
        $this->title = $title;
        $dsn = 'sqlite:' . dirname(__FILE__) .'/pastabap.db';
        try {
            $this->db = new PDO($dsn);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            die('Veritabanı açılamadı: ' . $e->getMessage());
        }
        $this->db->exec('pragma auto_vacuum = 1');
        $this->db->exec(
            "CREATE TABLE if not exists pastes (
                id PRIMARY KEY,
                ctitle TEXT,
                creation_date INTEGER,
                deletion_date INTEGER,
                paste BLOB
            );"
        );
        $this->db->exec(
            "CREATE TABLE if not exists users (
                hash PRIMARY KEY,
                nopaste_period INTEGER,
                degree INTEGER
            );"
        );
    }

    function __destruct() {
        $this->db = null;
    }

    private function add_content($content, $prepend = false) {
        if (!$prepend)
            $this->contents[] = $content;
        else
            array_unshift($this->contents, $content);
    }

    private function render() {
        print '<!DOCTYPE html>';
        print '<html class="tr-coretext tr-aa-subpixel">';
        print '<head>';
        print'<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        print '<title>'.htmlentities($this->title).'</title>';
        print '<link href="./css/style.css" rel="stylesheet" type="text/css" />';
		print '<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">';
        print '</head>';
        print '<body>';
        while (list(, $ct) = each($this->contents))
            print $ct;
        print '</body>';
        print '</html>';
        exit();
    }

    private function remaining_time($timestamp) {
        if ($timestamp == -1)
            return 'Never expires.';
        elseif ($timestamp == 0)
            return 'Expired.';
        elseif ($timestamp == -2)
            return 'One reading remaining.';

        $format = function($t,$s) { return $t ? $t.' '.$s.($t>1 ? 's' : '' ).' ' : ''; };

        $expiration = new DateTime('@'.$timestamp);
        $interval = $expiration->diff(new DateTime(), true);

        $ret = 'Expires in '.$format($interval->d, 'day');
        $ret .= $format($interval->h, 'hour');
        if ($interval->d === 0) {
            $ret .= $format($interval->i, 'minute');
            if ($interval->h === 0)
                $ret .= $format($interval->s, 'second');
        }
        return rtrim($ret).'.';
    }

    function prompt_paste() {
        $this->add_content(
            '<form method="post" action="." id="editForm">
                 <div id="left">
				 <input type="text" class="form-style" id="title" name="ctitle" placeholder="Başlık">
				 <input type="hidden" id="d" name="d" value="-1"/>
				 <button class="butt" type="submit"><i class="fa fa-paper-plane" aria-hidden="true"></i> Yolla gelsin</button>
                 </div>
                 <input type="text" id="sercankd" name="sercankd" placeholder="Do not fill me!" />
                 <div id="editor"></div>
				<input type="hidden" id="editortext" name="editortext"/>
             </form>'
        );
        $this->add_content('<script src="./js/ace/src-noconflict/ace.js"></script>');
        $this->add_content('<script src="./js/ace/src-noconflict/ext-language_tools.js"></script>');
		$this->add_content('<script src="./js/trmix.min.js"></script>');
        $this->add_content('<script>
								ace.require("ace/ext/language_tools");
								var editor = ace.edit("editor");
								editor.setTheme("ace/theme/se38");
								editor.getSession().setMode("ace/mode/abap");
								editor.setShowPrintMargin(true);
								editor.setPrintMarginColumn(72);
								editor.setOptions({
								  fixedWidthGutter: true,
								  enableBasicAutocompletion: true,
								  enableSnippets: true,
								  enableLiveAutocompletion: true
								});
								<!-- editor.setReadOnly(true); -->
								// added event handler
								document.getElementById("editForm").onsubmit = function(evt) {
								  document.getElementById("editortext").value = editor.getValue();
								}
							</script>');

        $this->render();
    }

    private function generate_id() {
        $query = $this->db->prepare(
            "SELECT id FROM pastes
             WHERE id = :uniqid;"
        );
        $query->bindParam(':uniqid', $uniqid, PDO::PARAM_STR, 6);

        do {
            $uniqid = substr(uniqid(), -6);
            $query->execute();
        } while ($query->fetch() != false);

        return $uniqid;
    }

    private function nopaste_period($degree) {
        return time() + intval(pow($degree, 2.5));
    }

    private function check_spammer() {
        $hash = sha1($_SERVER['REMOTE_ADDR']);

        $query = $this->db->prepare(
            "SELECT * FROM users
             WHERE hash = :hash"
        );
        $query->bindValue(':hash', $hash, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        $in_period = (!empty($result) and time() < $result['nopaste_period']);
        $obvious_spam = (!isset($_POST['sercankd']) or !empty($_POST['sercankd']));

        $degree = $in_period ? $result['degree']+1 : ($obvious_spam ? 512 : 1);
        $nopaste_period = $this->nopaste_period($degree);

        $query = $this->db->prepare(
            "REPLACE INTO users
             (hash, nopaste_period, degree)
             VALUES (:hash, :nopaste_period, :degree);"
        );
        $query->bindValue(':hash', $hash, PDO::PARAM_STR);
        $query->bindValue(':nopaste_period', $nopaste_period, PDO::PARAM_INT);
        $query->bindValue(':degree', $degree, PDO::PARAM_INT);
        $query->execute();

        if ($in_period or $obvious_spam)
            die('Spam');
    }

    function add_paste($deletion_date, $ctitle, $paste) {
        $this->check_spammer();

        $deletion_date = intval($deletion_date);
		$creation_date = time();
        if ($deletion_date > 0)
            $deletion_date += time();

        $uniqid = $this->generate_id();

        $query = $this->db->prepare(
            "INSERT INTO pastes (id, ctitle, creation_date, deletion_date, paste)
             VALUES (:uniqid, :ctitle, :creation_date, :deletion_date, :paste);"
        );
        $query->bindValue(':uniqid', $uniqid, PDO::PARAM_STR);
        $query->bindValue(':creation_date', $creation_date, PDO::PARAM_INT);
        $query->bindValue(':deletion_date', $deletion_date, PDO::PARAM_INT);
		$query->bindValue(':ctitle', $ctitle, PDO::PARAM_STR);
        $query->bindValue(':paste', $paste, PDO::PARAM_STR);
        $query->execute();

        header('location: ./' . $uniqid);
    }

    function show_paste($param) {
        $id = str_replace("@raw", "", $param);
        $is_raw = intval(strtolower(substr($param, -4)) == "@raw");

        $fail = false;
        $query = $this->db->prepare(
            "SELECT * FROM pastes
             WHERE id = :id;"
        );
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch();

        if ($result == null) {
            $fail = true;
        } elseif ($result['deletion_date'] < time()
                and $result['deletion_date'] >= 0) {
            $query = $this->db->prepare(
                "DELETE FROM pastes
                 WHERE id = :id;"
            );
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();

            /* do not fail on "burn after reading" pastes */
            if ($result['deletion_date'] != 0)
                $fail = true;
        } elseif ($result['deletion_date'] == -2) {
            $query = $this->db->prepare(
                "UPDATE pastes
                 SET deletion_date=0
                 WHERE id = :id;"
            );
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
        }

        if ($fail) {
            $this->add_content('<div id="warning">kod sayfası bulunamadı :(</div>');
            $this->prompt_paste();
        } else {
            header('X-Content-Type-Options: nosniff');

            if ($is_raw) {
                header('Content-Type: text/plain; charset=utf-8');

                print $result['paste'];
                exit();
            } else {
                header('Content-Type: text/html; charset=utf-8');

                // if ($result['highlighting']) {
                    // $this->add_content('<script>window.onload=function(){prettyPrint();}</script>');
                    // $this->add_content('<script defer src="./js/prettify.js"></script>', true);
                // }
				$ct = $result['creation_date'];
				$dt = new DateTime("@$ct");
				
                $this->add_content(
                    '<div id="left"><h2>'.$result['ctitle'].'</h2></div>
                     <div id="right">
					 <span class="datetime">'.$dt->format('d M Y H:i:s').'</span>
					 <a class="button" href="./"><i class="fa fa-file" aria-hidden="true"></i> Yeni Kod Ekle</a> 
					 <a class="button" href="./'.$id.'@raw"><i class="fa fa-font" aria-hidden="true"></i> Raw Görünüm</a> 
					 
					 </div>'
                );
                $class = 'prettyprint linenums';
                // if ($result['wrap'])
                    // $class .= ' wrap';

                 $this->add_content('<div id="editor">'.htmlentities($result['paste']).'</div>');
        $this->add_content('<script src="./js/ace/src-noconflict/ace.js"></script>');
        $this->add_content('<script src="./js/ace/src-noconflict/ext-language_tools.js"></script>');
        $this->add_content('<script src="./js/trmix.min.js"></script>');
        $this->add_content('<script>
								var editor = ace.edit("editor");
								editor.setTheme("ace/theme/se38");
								editor.getSession().setMode("ace/mode/abap");
								editor.setShowPrintMargin(true);
								editor.setPrintMarginColumn(72);
								// editor.setOptions({
								  // fixedWidthGutter: true,
								// });
								editor.setReadOnly(true);
							</script>');
            }
        }

        $this->render();
    }

    function cron() {
        $this->db->exec(
            "DELETE FROM pastes
             WHERE deletion_date > 0
             AND strftime ('%s','now') > deletion_date;
             DELETE FROM users
             WHERE strftime ('%s','now') > nopaste_period;"
        );
    }
}

$pastebin = new Pasthis();

if (php_sapi_name() == 'cli') {
    $pastebin->cron();
    exit();
}

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on')
    exit('Meh, not accessed over HTTPS.');

if (isset($_GET['p']))
    $pastebin->show_paste($_GET['p']);
elseif (isset($_POST['d']) && isset($_POST['editortext']))
    $pastebin->add_paste($_POST['d'], $_POST['ctitle'], $_POST['editortext']);
else
    $pastebin->prompt_paste();
?>
