// t. schneider tes@mit.edu 4.23.08
// laboratory for autonomous marine sensing systems
// massachusetts institute of technology 
// 
// this is iMOOS2SQL.h 
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

#ifndef IMOOS2SQLH
#define IMOOS2SQLH

#include "MOOSLIB/MOOSLib.h"
#include "MBUtils.h"
//#include "terminalio.h"
#include <iterator>
#include "MOOSUtilityLib/MOOSGeodesy.h"
#include <vector>
#include <iostream>

#include "iMOOS2SQL_config.pb.h"

#include "goby/version.h"
#if GOBY_VERSION_MAJOR >= 2 
 #include "goby/moos/goby_moos_app.h"
#else
 #include "goby/moos/libmoos_util/tes_moos_app.h"
#endif



// for mysql C API
#include "mysql/my_global.h"
#include "mysql/my_sys.h"
#include "mysql/mysql.h"

class CiMOOS2SQL :
#if GOBY_VERSION_MAJOR < 2
    public TesMoosApp {
    typedef TesMoosApp GobyMOOSApp;
#else
    public GobyMOOSApp  {       
#endif
  public:
    static CiMOOS2SQL* get_instance();


	
  private:
    void inbox(const CMOOSMsg& msg);
    void loop();
    void read_configuration();
    void do_subscriptions();

    
    void print_error (MYSQL *conn, const char *message);

    bool check_blackout(std::string vid, double report_time);
        
    std::string check_vid(std::string vnametype);
    bool parse_ais(std::string sdata);
    void change_vid2mmsi(std::string vid, std::string vmmsi);  
    std::string replace_vehicle_entry(std::string vtype, std::string vname, std::string newvid, std::string vloa, std::string vbeam);

    std::string escape(const std::string & query);
    
    void subscribe(const std::string& var)
    { GobyMOOSApp::subscribe(var, &CiMOOS2SQL::inbox, this); }

    //standard construction and destruction
    CiMOOS2SQL();
    virtual ~CiMOOS2SQL();

  private:
    //std::string m_host_name;      /* server host (default=localhost) */
    //std::string m_user_name;      /* username (default=login name) */
    //std::string m_password;     /* password (default=none) */
    //int m_port_num;   /* port number (use built-in value) */
    //std::string m_core_db_name;        /* database name (default=none) */
    MYSQL * core_conn;

    //std::string m_status_var;      //status_var name (default AIS_REPORT)
    //bool m_simulation;
    int m_simulation_user;
    
    struct moos_var
    {
        std::string db_name;
        double blackout_time;
        double last_publish;
    };

    // map a moos var to its corresponding struct (containing which db, blackout info)
    std::map<std::string, moos_var> m_var_list;

    // map the database name to its corresponding connection
    std::map<std::string, MYSQL*> m_conn;
    
    
    //bool m_geo_ok;
    //bool m_parse_ais;
    // std::string m_cruise_id;

    CMOOSGeodesy m_geodesy;    
    
    // vector of vehicle id strings of the vehicles we have seen
    std::vector<std::string> m_known_vid;
    // the last published time for that vehicle ID (to ensure the 1 sec window)
    std::vector<double> m_known_time;

    //vector of vehiclename.vehicletype of the vehicles we have seen
    std::vector<std::string> m_known_vname;

    static iMOOS2SQLConfig cfg_;
    static CiMOOS2SQL* inst_;    

};

#endif 
