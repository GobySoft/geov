<?php
require_once('connections/mysql.php');
require_once('includes/geov_html_writer.php');

$html = new geov_html_writer();

include "includes/geov_header.php";

$html->h2("google earth interface for ocean vehicles (geov).");

$html->hr();

$html->navbar("home");

$html->hr();


$html->p("geov (pronounced \"jove\") is an extensible viewer based on google earth and php/mysql that allows both realtime and historical viewing of ocean vehicles. it is presently primarily a research tool for autonomous underwater vehicles and surface craft.");

$html->h3("quick start:");
$html->push("ul");
$html->push("li");
$html->insert("ensure the vehicle data is being imported into geov's database. for MOOS, this means running iMOOS2SQL (");
$html->a("example .moos block", array("href" => "extras/iMOOS2SQL.moos"));
$html->insert(").");
$html->pop(); // </li>
$html->push("li");
$html->insert("create a");
$html->a("profile", array("href" => "profile.php"));
$html->insert("with the vehicles you want to see. be sure to bind the ip of the machine you wish to run google earth on.");
$html->pop(); // </li>
$html->push("li");
$html->insert("download the ");
$html->a("geov kml file", array("href" => "dl_kml.php"));
$html->insert("to your computer.");
$html->pop(); // </li>

$html->li("run google earth and open the file you just downloaded (file | open).");

$html->pop(); // </ul>

$html->hr();

$html->h3("news:");

$html->push("p");
$html->insert("3.10.25: README added to Github page: https://github.com/GobySoft/geov, including script on how to install a local GEOV instance."));
$html->insert(".");
$html->pop(); // </p>


$html->push("p");
$html->insert("2.9.09: google earth version 5.0 released with support for bathymetry. geov has been updated to include depth display (see screenshot below). numerous bugs fixed and new features added: ");
$html->a("release notes", array("href"=>"release_notes.txt"));
$html->insert(".");
$html->pop(); // </p>


$html->push("p");
$html->insert("10.20.08: data from glint (alliance server only) merged.");
$html->pop(); // </p>


$html->push("p");
$html->insert("6.27.08: two new modules added in the last week: moos_nafcon_target shows vectors for target tracking using ONR PLUS messages. moos_opgrid overlays an operation region and (optionally) an xy grid.");
$html->pop(); // </p>

$html->push("p");
$html->insert("6.23.08: server moved (physically) causing DNS change from aubergine.mit.edu -> aubergine.whoi.edu.");
$html->pop(); // </p>

$html->push("p");
$html->insert("5.21.08: pAIS2SQL deprecated. use iMOOS2SQL which has more features and is backwards compatible.");
$html->pop(); // </p>

$html->push("p");
$html->insert("4.21.08: google earth version 4.3 released and geov has been updated to work with it. this new version of ge has several new features and bug fixes so go ");
$html->a("download", array("href" => "http://earth.google.com"));
$html->insert(" it.");
$html->pop(); // </p>


$html->hr();

$html->p("example screenshot of new depth display for GE 5 (realtime / playback mode):");
$html->push("p");
$html->push("a", array("href"=>"images/screenshot4-full.jpg"));
$html->img(array("src" => "images/screenshot4.jpg", "alt" => "screenshot 4"));
$html->pop(); // a

$html->p("example screenshots of geov during cclnet08 exercises in la spezia, italy (realtime / playback mode):");

$html->push("p");
$html->push("a", array("href"=>"images/screenshot-full.jpg"));
$html->img(array("src" => "images/screenshot.jpg", "alt" => "screenshot"));
$html->pop(); // a
$html->push("a", array("href"=>"images/screenshot1-full.jpg"));
$html->img(array("src" => "images/screenshot1.jpg", "alt" => "screenshot 1"));
$html->pop(); // a
$html->push("a", array("href"=>"images/screenshot2-full.jpg"));
$html->img(array("src" => "images/screenshot2.jpg", "alt" => "screenshot 2"));
$html->pop(); // a
$html->pop(); // p

$html->p("example screenshot of history mode (simulated vehicles):");

$html->push("p");
$html->push("a", array("href"=>"images/screenshot3-full.jpg"));
$html->img(array("src" => "images/screenshot3.jpg", "alt" => "screenshot 3"));
$html->pop(); // a
$html->pop(); // p

$html->hr();

include "includes/geov_footer.php";

// closes all tags
$html->echo_html();

?>