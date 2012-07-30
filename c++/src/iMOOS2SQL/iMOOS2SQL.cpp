// t. schneider tes@mit.edu 4.23.08
// laboratory for autonomous marine sensing systems
// massachusetts institute of technology 
// 
// this is iMOOS2SQL.cpp 
//
// see the readme file within this directory for information
// pertaining to usage and purpose of this script.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This software is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this software.  If not, see <http://www.gnu.org/licenses/>.


#include "iMOOS2SQL.h"

// define the blackout time for new results to be published to the mysql db
// (in seconds per vehicle)

#ifndef _WIN32
#include <netdb.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#endif

#if GOBY_VERSION_MAJOR >= 2 
#include "goby/common/logger.h"
using namespace goby::common::logger;
using goby::common::Colors;
#else
#include "goby/util/logger.h"
using goby::util::Colors;
#endif

const unsigned CORE_BLACKOUT_TIME = 5;
const std::string GE_CLIENT_ID = "2";

using namespace std;
using goby::util::as;
using goby::util::glogger;

CiMOOS2SQL* CiMOOS2SQL::inst_ = 0;

CiMOOS2SQL* CiMOOS2SQL::get_instance(iMOOS2SQLConfig* cfg)
{
    if(!inst_)
        inst_ = new CiMOOS2SQL(cfg);
    return inst_;
}

CiMOOS2SQL::CiMOOS2SQL(iMOOS2SQLConfig* cfg)
    : GobyMOOSApp(cfg),
	  cfg_(cfg)
{
    glogger() << "reading our configuration..." << std::endl;
    read_configuration();
    glogger() << "\t...success." << std::endl;

    glogger() << "doing subscriptions..." << std::endl;
    do_subscriptions();
    glogger() << "\t...success." << std::endl;
    
    /* initialize connection handler */
    core_conn = mysql_init (NULL);
    if (core_conn == NULL)
    {
        glogger()  << die << "mysql_init() failed (probably out of memory)" << std::endl;        
    }
    /* connect to mysql server */
    if (mysql_real_connect (core_conn,
                            cfg_->mysql_host().c_str(),
                            cfg_->mysql_user().c_str(),
                            cfg_->mysql_password().c_str(),
                            cfg_->mysql_core_db_name().c_str(),
                            cfg_->mysql_port(),
                            "/var/run/mysqld/mysqld.sock",
                            0) == NULL)
    {
        mysql_close (core_conn);
        glogger() << die << "core mysql_real_connect() failed\n"<< cfg_->DebugString() << std::endl;
    }

    /* select db */
    if (mysql_select_db(core_conn, cfg_->mysql_core_db_name().c_str()) != 0)
    {
        glogger() << die << "could not select core database" << std::endl;
    }
    glogger() << "successfully initialized and opened core mysql connection" << std::endl;
    
    
    // do connections for other databases
    map<string, MYSQL*>::iterator it;
    
    for ( it=m_conn.begin() ; it != m_conn.end(); it++ )
    {
        /* connect to mysql server */
        if (mysql_real_connect ((*it).second,
                                cfg_->mysql_host().c_str(),
                                cfg_->mysql_user().c_str(),
                                cfg_->mysql_password().c_str(),
                                (*it).first.c_str(),
                                cfg_->mysql_port(),
                                "/var/run/mysqld/mysqld.sock",
                                0) == NULL)
        {
            glogger() << die << "core mysql_real_connect() failed\n" << cfg_->DebugString() << std::endl;
        }
        
        /* select db */
        if (mysql_select_db((*it).second, (*it).first.c_str()) != 0)
        {
            glogger() << die << "could not select database " << (*it).first << std::endl;
            
        }
        glogger() << "successfully initialized and opened "<<(*it).first <<" mysql connection" << std::endl;
    }

    // default to real user
    m_simulation_user = 0;
    
    if(cfg_->simulation())
    {
#ifdef _WIN32
		glogger().is(goby::common::logger::DIE) && glogger() << "Simulation not supported in WIN32 version of iMOOS2SQL" << std::endl;
#elif
        MYSQL_RES *res_set;

        string query = "SELECT USER()";
        string ip, host;
        
        if (mysql_query(core_conn, query.c_str()) != 0)
        {
            print_error (core_conn, "mysql_query() failed");
        }
        else
        {
            res_set = mysql_store_result(core_conn);
            MYSQL_ROW row;
            row = mysql_fetch_row(res_set);
            string user = row[0];
            vector<string> user_parts;
            boost::split(user_parts, user, boost::is_any_of("@"));
            host = user_parts[1];

            struct hostent *hp = gethostbyname(host.c_str());
                
            if (hp == NULL)
            {
                cout << "gethostbyname() failed\n";
            }
            else
            {
                ip = inet_ntoa( *( struct in_addr*)( hp -> h_addr_list[0]));
            }
                
//                if(ip == "localhost")
//                    ip = "127.0.0.1";
                
        }
        query = "SELECT connected_userid, user_name FROM core_connected JOIN core_user ON user_id=connected_userid WHERE connected_ip = '" + ip + "' AND connected_client = " + GE_CLIENT_ID;
        
        glogger() << group("select") << query << std::endl;
        
        if (mysql_query(core_conn, query.c_str()) != 0)
        {
            print_error (core_conn, "mysql_query() failed");
        }
        else
        {
            res_set = mysql_store_result(core_conn);
            
            if(res_set == NULL)
                print_error(core_conn, "mysql_store_result() failed");
            else
            {    
                if(mysql_num_rows(res_set) == 0)
                {
                    glogger() << die << "no profile bound to this IP address (" << ip << ") for simulation use. you must bind such a profile first using the geov profile manager." << std::endl;
                        
                }
                else
                {
                    //get user id
                    MYSQL_ROW row;
                    row = mysql_fetch_row(res_set);
                    m_simulation_user = atoi(row[0]);
                    string sim_name = row[1];
                    glogger() << "inputting simulation data for user " << sim_name << "(" << m_simulation_user << ") at IP: " << ip << std::endl;
                }
            }
        }
#endif // simulation not supported
	}
}
CiMOOS2SQL::~CiMOOS2SQL()
{
    /* disconnect from server */
    mysql_close (core_conn);
}

// OnNewMail: called when new mail (previously registered for)
// has arrived.
void CiMOOS2SQL::inbox(const CMOOSMsg& msg)
{
    string key   = msg.GetKey(); 	
    string sdata = msg.GetString();
    double ddata = msg.GetDouble();
    
    bool is_dbl  = msg.IsDouble();
    bool is_str  = msg.IsString();
    
    
    //MOOSTrace("%s\n\n", sdata.c_str());
    
    
    // got a new AIS_REPORT
    if(MOOSStrCmp(key, cfg_->status_var()))
    {
        if(cfg_->parse_status())
            parse_ais(sdata);
    }
    // other stuff we've subscribed for
    else
    {
        string query_insert;

        double thistime = MOOSTime();
            
        // check blackout
        if(m_var_list[key].last_publish + m_var_list[key].blackout_time < thistime)
        {
            m_var_list[key].last_publish = thistime;
                
            //db name = geov_something
            //table name = something_data
            vector<string> parsed_name = chompString(m_var_list[key].db_name, '_');
                
            query_insert =                                          \
                "INSERT INTO "+parsed_name[1]+"_data (data_time, data_userid, data_variable, data_value) ";
                
            query_insert += "VALUES ('"+as<std::string>(msg.GetTime());
            query_insert += "', '" + as<std::string>(m_simulation_user);
            query_insert += "', '"+ escape(key);
                
            if(is_str)
            {
                glogger() << "(string)" << key << ": " << sdata << std::endl;
                query_insert += "', '"+escape(sdata);
            }
                
            if(is_dbl)
            {
                glogger() <<"(double)" << key << ": " << ddata << std::endl;
                query_insert += "', '"+as<std::string>(ddata);               
            }
                
            query_insert += "')";
                
            glogger() << group("insert") << query_insert << std::endl;
            
            if (mysql_query(m_conn[m_var_list[key].db_name], query_insert.c_str()) != 0)
            {
                print_error (m_conn[m_var_list[key].db_name], "insert failed");    
            }
        }
    }
}

void CiMOOS2SQL::read_configuration()
{
    glogger().add_group("insert", Colors::lt_magenta, "inserts");
    glogger().add_group("select", Colors::lt_blue, "selects");
    

    glogger() << "reading in geodesy information: " << std::endl;
    if (!cfg_->common().has_lat_origin() || !cfg_->common().has_lon_origin())
    {
        glogger() << die << "no lat_origin or lon_origin specified in configuration. this is required for geodesic conversion" << std::endl;
    }
    else
    {
        if(m_geodesy.Initialise(cfg_->common().lat_origin(), cfg_->common().lon_origin()))
            glogger() << "success!" << std::endl;
        else
            glogger() << die << "could not initialize geodesy" << std::endl;
    }


    for(int i = 0; i < cfg_->aux_db_size(); ++i)
    {
        m_conn[cfg_->aux_db(i).name()] = mysql_init(NULL);
        //initialize connection for auxiliary connections
        if (m_conn[cfg_->aux_db(i).name()] == NULL)
        {
            glogger() << die << "mysql_init() failed (probably out of memory)" << std::endl;
        }

        for(int j = 0; j < cfg_->aux_db(i).echo_size(); ++j)
        {
            m_var_list[cfg_->aux_db(i).echo(j).from_moos_var()].db_name = cfg_->aux_db(i).name();
            m_var_list[cfg_->aux_db(i).echo(j).from_moos_var()].blackout_time = cfg_->aux_db(i).echo(j).blackout_time();
            m_var_list[cfg_->aux_db(i).echo(j).from_moos_var()].last_publish = 0;
            glogger() << "Adding variable: " << cfg_->aux_db(i).echo(j).from_moos_var() << " to database: " << cfg_->aux_db(i).name() << " on blackout: " << cfg_->aux_db(i).echo(j).blackout_time() << std::endl;
        }
    }

    if (!cfg_->parse_status())
        glogger() << warn << "NOT parsing status report. set parse_status = true in .moos file to do so (you will not see vehicles)" << std::endl;

}

void CiMOOS2SQL::loop()
{ }

// DoRegistrations: register for MOOS variables we 
// want to hear about (receive mail for)
void CiMOOS2SQL::do_subscriptions()
{
    subscribe(cfg_->status_var());

    // register for other variables
    map<string, moos_var>::iterator it;
    
    for (it=m_var_list.begin(); it != m_var_list.end(); it++)
        subscribe((*it).first);
}


void CiMOOS2SQL::print_error (MYSQL *conn, const char *message)
{
    glogger() << message << std::endl;
    
    if (conn != NULL)
    {
#if MYSQL_VERSION_ID >= 40101
        glogger() << "Error" << mysql_errno (conn) << "(" << mysql_sqlstate(conn) << "): " << mysql_error (conn) << std::endl;
        
#else
        glogger() << "Error" << mysql_errno (conn) << ": " << mysql_error (conn) << std::endl;
#endif
    }
}



// check_blackout: check to see if a report for the vehicle has
// been published to the mysql db in within the CORE_BLACKOUT_TIME interval
// zero means OK to publish to mysql db, nonzero means not OK
bool CiMOOS2SQL::check_blackout(string vid, double report_time)
{
    unsigned int i;
  
    for(i=0; i<m_known_vid.size(); i++)
    {
        if(m_known_vid[i] == vid)
        {
          
            if (abs(m_known_time[i]-report_time) >= CORE_BLACKOUT_TIME)
            {
                //do it
                m_known_time[i] = report_time;
                return true;
            }
            else
            {
                //not enough time elapsed
                return false;
            }
        }
    }
    return true;
}

// matches a name.type string to a vehicle id to save database accesses
string CiMOOS2SQL::check_vid(string vnametype)
{  
    unsigned int i;

    for(i=0; i<m_known_vid.size(); i++)
    {
        if(m_known_vname[i] == vnametype)
        {
            return m_known_vid[i];        
        }
    }
  
    return "";
}


bool CiMOOS2SQL::parse_ais(string sdata)
{
            
    bool ok = true;
        
    // adapted from pTransponderAIS
    string vname;         bool vname_set   = false;
    string vtype;         bool vtype_set   = false;
    string vmmsi;         bool vmmsi_set   = false;
    string vbeam;         bool vbeam_set   = false;
    string vloa;          bool vloa_set   = false;
    string utc_time;      bool utc_time_set = false;
    string nav_x_val;     bool nav_x_set   = false;
    string nav_y_val;     bool nav_y_set   = false;
    string nav_lat_val;   bool nav_lat_set = false;
    string nav_long_val;  bool nav_long_set = false;
    string nav_spd_val;   bool nav_spd_set = false;
    string nav_hdg_val;   bool nav_hdg_set = false;
    string nav_dep_val;   bool nav_dep_set = false;

    vector<string> svector = parseString(sdata, ',');
    int vsize = svector.size();
    for(int i=0; i<vsize; i++) {
        vector<string> svector2 = parseString(svector[i], '=');
        if(svector2.size() == 2) {
            string left = tolower(stripBlankEnds(svector2[0]));
            string right = stripBlankEnds(svector2[1]);
            
            if(left=="name") {
                vname = right;
                vname_set = true;
            }
            if(left == "type") {
                vtype = right;
                vtype_set = true;
            }
            if(left == "mmsi") {
                vmmsi = right;
                vmmsi_set = true;
            }
            if(left == "beam") {
                vbeam = right;
                vbeam_set = true;
            }
            if(left == "loa") {
                vloa = right;
                vloa_set = true;
            }
            if(left == "utc_time") {
                utc_time = right;
                utc_time_set = true;
            }
            if(left == "x") {
                nav_x_val = right;
                nav_x_set = true;
            }
            if(left == "y") {
                nav_y_val = right;
                nav_y_set = true;
            }
            if(left == "lat") {
                nav_lat_val = right; 
                nav_lat_set = true;
            }
            if(left == "lon") {
                nav_long_val = right;
                nav_long_set = true;
            }
            if(left == "spd") {
                nav_spd_val = right;
                nav_spd_set = true;
            }
            if(left == "hdg") {
                nav_hdg_val = right;
                nav_hdg_set = true;
            }
            if(left == "depth") {
                nav_dep_val = right;
                nav_dep_set = true;
            }
        }
    }
        
    // require either the vmmsi or the name/type as identifier!
    if(!(vmmsi_set || (vname_set && vtype_set)))
        return false;

    // determine vehicle id or add new vehicle
    string vid;
    string vnametype = vname + vtype;

    if(!vmmsi_set)
        vid = check_vid(vnametype);
       
    if(vid == "")
    {
        MYSQL_RES *res_set;

		glogger() << group("select") << "vehicle_name: " << vname << std::endl;        
		glogger() << group("select") << "escaped vehicle_name: " << escape(vname) << std::endl;        

        string query_veh = "SELECT vehicle_id FROM core_vehicle WHERE ";
        query_veh += "(lower(vehicle_name) = '" + escape(tolower(vname));
        query_veh += "' AND lower(vehicle_type) = '" + escape(tolower(vtype));
        query_veh += "') OR vehicle_id = '" + escape(vmmsi) + "'";

        glogger() << group("select") << query_veh << std::endl;        
            
        if (mysql_query(core_conn, query_veh.c_str()) != 0)
        {
            print_error (core_conn, "mysql_query() failed");
        }
        else
        {
            res_set = mysql_store_result(core_conn);
                
            if(res_set == NULL)
                print_error(core_conn, "mysql_store_result() failed");
            else
            {
                // if you have no id already for this, or the vname and vtype aren't both set
                // you need to add an entry for this vehicle
                if(mysql_num_rows(res_set) == 0)
                {
                    if(!vmmsi_set)
                    {
                        MYSQL_RES *res_set2;
                        
                        string newvid;
                        
                        string query_find_next_id = "SELECT MAX(vehicle_id)+1 FROM core_vehicle WHERE vehicle_id < 100000000";
                        if (mysql_query(core_conn, query_find_next_id.c_str()) != 0)
                            print_error (core_conn, "mysql_query() failed");
                        else
                        {
                            res_set2 = mysql_store_result(core_conn);
                            MYSQL_ROW row = mysql_fetch_row(res_set2);
                            newvid = row[0];
                        }
                        
                        vid = replace_vehicle_entry(vtype, vname, newvid, vloa, vbeam);
                    }
                    else
                    {
                        vid = replace_vehicle_entry(vmmsi, vmmsi, vmmsi, "", "");
                    }
                }
                else if(!(mysql_num_rows(res_set) == 0))
                {
                    //get veh id
                    MYSQL_ROW row;
                    row = mysql_fetch_row(res_set);
                    vid = row[0];
                }

                glogger() << "vehicle id is " << vid << "." << std::endl;
                    
                mysql_free_result(res_set);
            }
        }
            
        m_known_vid.push_back(vid);
        m_known_time.push_back(0);
        m_known_vname.push_back(vnametype);
    }

    if(vmmsi_set && vname_set && vtype_set)
    {
        // update MMSI entries for previously known vehicles
        if(vmmsi_set && vid != vmmsi)
        {
            change_vid2mmsi(vid, vmmsi);
        }
        
        vid = replace_vehicle_entry(vtype, vname, vmmsi, vloa, vbeam);
    }
    else if(vmmsi_set)
        vid = vmmsi;
    

    // check the blackout time on this vehicle. if not enough time elapsed
    // return to stop wasting our time :) since we aren't going to publish
    // to the mysql db
    if (!check_blackout(vid, atof(utc_time.c_str())))
        return true;

        
    string nlat;
    string nlong;
        
    if(!nav_lat_set || !nav_long_set)
    {
        if(!nav_x_set || !nav_y_set)
            ok = false;
        else
        {
            double dnlat;
            double dnlong;
            //get lat/long from geodesy         
            if(!m_geodesy.UTM2LatLong(atof(nav_x_val.c_str()), atof(nav_y_val.c_str()), dnlat, dnlong))
            {
                glogger() << warn << "Geodesy conversion failed" << std::endl;
                return false;
            }
            else
            {
                nlat = as<std::string>(dnlat);
                nlong = as<std::string>(dnlong);
            }
        }
    }
    else
    {
        nlat = nav_lat_val;
        nlong = nav_long_val;
    }

    if(ok)
    {

        string table = "core_data";
        string query_insert = \
            "INSERT INTO " + table + " (data_vehicleid, ";
        query_insert += "data_userid, data_time, data_lat, data_long, data_heading, data_speed, data_depth ) ";
        query_insert += "VALUES ('"+escape(vid);
        query_insert += "', '" + as<std::string>(m_simulation_user);
        query_insert += "', '"+escape(utc_time);
        query_insert += "', '"+escape(nlat);
        query_insert += "', '"+escape(nlong);
        query_insert += "', '"+escape(nav_hdg_val);
        query_insert += "', '"+escape(nav_spd_val);
        query_insert += "', '" +escape(nav_dep_val);
        query_insert += "')";

        glogger() << group("insert") << query_insert << std::endl;
        
        if (mysql_query(core_conn, query_insert.c_str()) != 0)
        {
            print_error (core_conn, "insert failed");
        }    

    }
    else
    {
        glogger() << warn << "AIS_REPORT received lacking information" << std::endl;
    }

    return true;
    
}

void CiMOOS2SQL::change_vid2mmsi(std::string vid, std::string vmmsi)
{
    vid = escape(vid);
    vmmsi = escape(vmmsi);
    
    glogger() << "changing vehicle id to MMSI for previously known vehicle " << vid << std::endl;
    
    string query = "UPDATE core_vehicle SET vehicle_id='" + vmmsi + "' ";
    query += "WHERE vehicle_id='" + vid + "'";

    glogger() << query << std::endl;
        
    if (mysql_query(core_conn, query.c_str()) != 0)
        print_error (core_conn, "mysql_query() failed");
    
    query = "UPDATE core_data SET data_vehicleid='" + vmmsi + "' ";
    query += "WHERE data_vehicleid='" + vid + "'";
    
    glogger() << query << std::endl;
    
    if (mysql_query(core_conn, query.c_str()) != 0)
        print_error (core_conn, "mysql_query() failed");
    
    query = "UPDATE core_connected_vehicle SET c_vehicle_vehicleid='" + vmmsi + "' ";
    query += "WHERE c_vehicle_vehicleid='" + vid + "'";
    
    glogger() << query << std::endl;
    
    if (mysql_query(core_conn, query.c_str()) != 0)
        print_error (core_conn, "mysql_query() failed");


    query = "UPDATE core_profile_vehicle SET p_vehicle_vehicleid='" + vmmsi + "' ";
    query += "WHERE p_vehicle_vehicleid='" + vid + "'";
    
    glogger() << query << std::endl;
    
    if (mysql_query(core_conn, query.c_str()) != 0)
        print_error (core_conn, "mysql_query() failed");

}



std::string CiMOOS2SQL::replace_vehicle_entry(std::string vtype, std::string vname, std::string newvid, std::string vloa, std::string vbeam)
{
    //add new id
    string query_veh_insert = "REPLACE INTO core_vehicle (vehicle_type, vehicle_name, vehicle_id";
    if(vloa != "")
        query_veh_insert += ",vehicle_loa";
    if(vbeam != "")
        query_veh_insert += ",vehicle_beam";
    
    query_veh_insert +=  ") ";
    query_veh_insert += "VALUES ('" + escape(tolower(vtype));
    query_veh_insert += "', '" + escape(tolower(vname));                    
    query_veh_insert += "', '" + escape(newvid);
    
    if(vloa != "")
        query_veh_insert += "', '" + escape(vloa);
    if(vbeam != "")
        query_veh_insert += "', '" + escape(vbeam);
    
    query_veh_insert += "')";
    

    glogger() << group("insert") << query_veh_insert << std::endl;
        
    
    if (mysql_query(core_conn, query_veh_insert.c_str()) != 0)
    {
        print_error (core_conn, "insert failed");
        return "0";
    }       
    else
    {
        return as<std::string>(mysql_insert_id(core_conn));
    }
}


std::string CiMOOS2SQL::escape(const std::string & s)
{
    unsigned long len = s.length();
	std::string c(len*2, '\0');
    mysql_real_escape_string(core_conn, &c[0], s.c_str(), len);
	return std::string(c.c_str());
}
