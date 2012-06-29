cmake_minimum_required(VERSION 2.6.0 FATAL_ERROR)
project(geov_c++)

## allows us to write custom modules or modifying existing ones
set(CMAKE_MODULE_PATH "${CMAKE_CURRENT_SOURCE_DIR}/cmake_modules/")


find_package(MOOS REQUIRED)
include_directories(${MOOS_INCLUDE_DIRS})


find_package(Goby REQUIRED)
include_directories(${GOBY_INCLUDE_DIRS})

find_package(ProtobufLocal REQUIRED)
include_directories(${PROTOBUF_INCLUDE_DIRS})

## start adding subdirectories
add_subdirectory(src)
