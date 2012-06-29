cmake_minimum_required(VERSION 2.6.0 FATAL_ERROR)
project(geov_c++)

## allows us to write custom modules or modifying existing ones
set(CMAKE_MODULE_PATH "${CMAKE_CURRENT_SOURCE_DIR}/cmake_modules/")




if(${WIN32})
  add_definitions (-DNOMINMAX -D_WIN32_WINNT=0x0501 -DWIN32_LEAN_AND_MEAN -D_WINUSER_)
  add_definitions( -DBOOST_ALL_NO_LIB)
  set( Boost_USE_STATIC_LIBS ON )
endif()

find_package(Boost 1.40.0 COMPONENTS system date_time thread program_options filesystem signals regex)
include_directories(${Boost_INCLUDE_DIRS})

find_package(MOOS REQUIRED)
include_directories(${MOOS_INCLUDE_DIRS})

find_package(Goby REQUIRED)
include_directories(${GOBY_INCLUDE_DIRS})

find_package(ProtobufLocal REQUIRED)
include_directories(${PROTOBUF_INCLUDE_DIRS})

## start adding subdirectories
add_subdirectory(src)
