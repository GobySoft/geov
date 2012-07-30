
find_library(mysqlclient_LIB libmysql)
find_path(mysqlclient_PATH mysql/mysql.h)
include_directories(${mysqlclient_PATH}/mysql)
  
  
add_subdirectory(iMOOS2SQL)
