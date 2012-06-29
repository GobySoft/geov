#--------------------------------------------------------
# The CMakeLists.txt for:                       iMOOS2SQL
# Author(s):                                 t. schneider
#--------------------------------------------------------

# mysql client library
find_library(mysqlclient_LIB mysqlclient)  
find_path(mysqlclient_PATH mysql/mysql.h)

message(${mysqlclient_PATH})

if(mysqlclient_LIB AND mysqlclient_PATH)
  file(GLOB PROTOS ${CMAKE_CURRENT_SOURCE_DIR}/*.proto)  
  protobuf_generate_cpp(PROTO_SRCS PROTO_HDRS ${CMAKE_CURRENT_BINARY_DIR} ${PROTOS})
  
  file(GLOB SRC *.cpp)

  include_directories(${mysqlclient_PATH}/mysql)
  
  add_executable(iMOOS2SQL ${SRC} ${PROTO_SRCS} ${PROTO_HDRS})
  
  target_link_libraries(iMOOS2SQL
    ${mysqlclient_LIB}
    ${MOOS_LIBRARIES}
    ${IVP_MBUTIL_LIBRARY}
    ${GOBY_LIBRARIES}
    ${Boost_LIBRARIES}
    ${PROTOBUF_LIBRARIES}
    )
endif()
