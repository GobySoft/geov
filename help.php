<?php
  /************************************************************************************
   t. schneider | tes at mit.edu | 2.7.08
   laboratory for autonomous marine sensing systems

   help.php - instructions for using geov
  ************************************************************************************/

  /************************************************************************************
   definitions
  ************************************************************************************/

/************************************************************************************
 connections
************************************************************************************/
require_once('connections/mysql.php');
require_once('includes/geov_html_writer.php');

$html = new geov_html_writer();

include "includes/geov_header.php";

$html->h2("instructions");

$html->hr();
$html->navbar("instructions");
$html->hr();

$html->p("<strong>a note:</strong> i have many projects i'm currently working on, of which geov is only one. geov is still in a fast paced development stage with a single developer (me). thus, the sad truth is that documentation will always tend to lag behind implementation. so take these instructions with a grain of salt and if things seem inconsistent, please do not hesitate to email me with questions [tes at mit.edu]. i will answer them, i promise.");

$html->hr();


$html->h3("what does this do?");
$html->p("the google earth interface for ocean vehicles (geov) allows you to visualize the location of ocean craft. you can view positions of the vehicles as they are known (realtime) or visualize historical data of any known vehicles. it is highly configurable, allowing the user to visualize the current vehicle's location and a trail of its past position.");

$html->hr();

$html->h3("how do i use it?");
$html->p("geov is comprised of this webpage interface (accessible from any web browser) and a script (kml) running in google earth. google earth is freely downloadable from ".a_href("http://earth.google.com/","http://earth.google.com/").".");

$html->p("follow these steps to use geov (you may find the section \"how does this work\" useful as well):");

$html->h4("&#187; get data into the geov database");

$html->p("geov stores all its data in a ".a_href("http://www.mysql.com/", "MySQL")." database. currently, two mechanisms exist for importing data. the first is a realtime tool (iMOOS2SQL) intended to interface a MOOS database containing AIS_REPORT variables  (and other data, as needed for various modules) with the geov database. (for details on MOOS and MOOS-IvP, go to the authors' websites (".a_href("http://www.robots.ox.ac.uk/%7Epnewman/TheMOOS/index.html", "Paul Newman").", ".a_href("http://oceanai.mit.edu/mikerb/software/home.html", "Mike Benjamin").")). ");


$html->p("iMOOS2SQL takes all AIS_REPORT variables (which are published primarily by pTransponderAIS) in the community it is run and imports them into the geov database specified in the .moos file at a maximum frequency of once per second. iMOOS2SQL is undergoing continued development, so anyone without access to the mit moos svn repository should contact the author (tes at mit.edu) for the latest version the second tool is available on this website \"import .alog\" and is used to import vehicle logs from pLogger, thereby allowing data that is inaccessible during operations to be viewed later in playback mode. ");

$html->p("geov is not connected to the MOOS in any way beyond these import tools, thus making it possible to easily support data from other software systems. anyone interested in writing an import tool for a different source should contact the author (tes at mit.edu) for assistance.");

$html->h4("&#187; create a profile that determines the data you wish to see");

$html->p("go to the profile manager and create a user name (or log in with one you already have). profiles are grouped by user, but it is possible to make copies of profiles for other users. choose the type of profile you wish to create: ");

$html->p("<strong>realtime:</strong> attempts to show vehicle position as recently as possible from the server's system clock. in this mode you are seeing what is happening right now.");
$html->p("<strong>playback:</strong> mimicks realtime, but for point in the past. this mode is like a video recorder playback.");
$html->p("<strong>history:</strong> gives the trails for specified vehicles that can be manipulated in google earth with the time slider. this mode is somewhat analogous to a video editing program.");

$html->p("once you have named your profile, you are in the profile editing screen. you should see a table of all the known vehicles. in order to see any vehicles in google earth, you must check at least one of the \"show _\" boxes. ");

$html->p("available options (note that some options are not available for all modes:");
$html->p("<i>show vehicle image:</i> displays a graphic representing the vehicle on the map. image scale modifies the size of this image relative to the vehicle's real size.");
$html->p("<i>show vehicle name:</i> displays text with the vehicle's name on the map. this item maintains its size no matter the zoom level.");
$html->p("<i>show points:</i> show a trail of points with the position history of the vehicle. each point represents an update from the vehicle. ");
$html->p("<i>show lines:</i> show the lines that interpolate between the update points. this is helpful for vehicles with infrequent updates. ");
$html->p("<i>color:</i> the color of the objects for that vehicle.");
$html->p("<i>trail decay:</i> the time (in seconds) that the points or lines stay on the screen for. (that is, anything older than trail decay seconds ago is not displayed).");
$html->p("<i>track:</i> keep the current vehicle in view at all times (overrides the ability to free fly in google earth) if the track bubble is set to \"all\", the camera is freed again.");
$html->p("<i>track follows vehicle's current heading:</i> forces your view to point in the heading of the tracked vehicle.");

$html->h4("&#187; bind the profile to the ip address of the machine running google earth");

$html->p("on the profile manager page, you must either check the box for \"use this machine's ip\" or enter another ip if you wish to run google earth on a different machine from the one you are currently running the web page interface on. use /sbin/ifconfig on GNU/Linux or ipconfig in Windows to determine your ip address.");

$html->p("a given machine can only be bound to a single profile. if you change profiles and bind your machine again, all previous bindings are removed. this simply means you can only view one profile at a time for each machine running google earth. in practice, just ensure that you remember to see that the binding is set (checkbox is checked or ip values entered) for that profile in order to view it.");

$html->h4("&#187; download and open the geov kml file");

$html->p("click the link on the navigation bar (called \"download geov kml\") to download the geov kml (keyhole markup language, which is google earth's data format) file. save it to your computer. open google earth, then use the file | open dialog to open the file you just downloaded (geov_core_{servername}.kml). please note that this file is specific to the server geov is running on, so you would need to download a new file for a cruise that is using a different server. ");

$html->p("geov is now running in google earth. assuming you have completed the above steps, you should now see vehicles in the places pane (part of the bar on the left side). to zoom quickly to the part of the world of interest, double click the name of one of the vehicles to fly to that location.");

$html->p("for playback mode, you must launch the playback controls from the profile page. this will open a new web browser window which you may resize to your liking. the playback controls are much like a DVD player, etc. you must push play in order to see any vehicles in the view");

$html->p("for history mode, you will want to use the time slider within google earth to change what you see. you can also use the playback mode on that time slider. be sure to use the advanced options button next to the time slider to adjust playback speed and other useful options");

$html->hr();

$html->h3("troubleshooting");

$html->p("<strong>no vehicles present in google earth:</strong> go to the profile manager and ensure that you have selected at least one vehicle for viewing (\"show name, show image, etc\") and that you have clicked save and refresh after doing so. also ensure that you have bound your ip to this profile (see above). ");

$html->p("<strong>google earth gives me an error:</strong> this software is still in the development stage and thus some bugs are to be expected. use google earth's \"ignore\" or \"continue\" button in the error dialog and see if the error is resolved. if not, close google earth, wait at least ten seconds and reopen it. bug reports to tes at mit.edu are welcome.");

$html->hr();

$html->h3("tips &amp; advanced features");

$html->p("<strong>profile manager:</strong> click \"name\", \"type\", or \"owner\" to sort (ascending) all the visible vehicles on the display.");

$html->p("use the boxes below the label \"all\" to make changes at once to all the visible vehicles.");

$html->p("<strong>google earth:</strong> use the tilt control (top slider in the upper right) to go to a ".
  "3D view of the world.");


$html->p("go to tools | options and change settings. try changing the fly-to speed under touring to 0.5. bump up the cache values to maximum in order to use google earth without an internet connection most effectively. google earth will cache areas you look at so if you want to use geov on a cruise without internet, simply zoom around the area you will be using while you have internet, then it will be available when you do not.");

$html->hr();

$html->h3("how does this work?");

$html->p("the basic functionality for geov is based on the ability for google earth to read in specially formatted xml files (kml) to display such things as lines, polygons, and images overlaid on specific latitudes / longitudes. one of the kml tags, NetworkLinkControl, causes google earth to query a specified webserver for a kml file to display. thus, the kml file you download and open in google earth only contains the code that forces google earth to query (every second) this webserver, which forms the other half of geov.");

$html->p("the webserver (apache2) is running php and mysql. php is a scripting language that can produce xml and html (or most any other desired output). mysql is a fast access database that interfaces with php (among other languages). thus, all the data for geov is stored in the mysql database (by iMOOS2SQL or a similar process). the profile manager stores information about what a given user wants to see, which modifies the output passed by php to google earth when it is queried. the ip address of the machine running google earth tells php which profile to use when determining which data to send.");  

$html->p("(insert data flow diagram)");
$html->hr();


$html->echo_html();

?>