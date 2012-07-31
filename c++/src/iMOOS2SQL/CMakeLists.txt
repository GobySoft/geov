#--------------------------------------------------------
# The CMakeLists.txt for:                       iMOOS2SQL
# Author(s):                                 t. schneider
#--------------------------------------------------------

# mysql client library

if(mysqlclient_LIB AND mysqlclient_PATH)
  file(GLOB PROTOS ${CMAKE_CURRENT_SOURCE_DIR}/*.proto)  
  protobuf_generate_cpp(PROTO_SRCS PROTO_HDRS ${CMAKE_CURRENT_BINARY_DIR} ${PROTOS})
  
  file(GLOB SRC *.cpp)

  
  add_executable(iMOOS2SQL ${SRC} ${PROTO_SRCS} ${PROTO_HDRS})
  
  target_link_libraries(iMOOS2SQL
    ${mysqlclient_LIB}
    ${MOOS_LIBRARIES}
    ${IVP_MBUTIL_LIBRARY}
    ${GOBY_LIBRARIES}
    ${Boost_LIBRARIES}
    ${PROTOBUF_LIBRARIES}
    ssl
    z
    )
endif()

install(TARGETS iMOOS2SQL RUNTIME DESTINATION bin)
