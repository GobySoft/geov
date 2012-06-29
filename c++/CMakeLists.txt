cmake_minimum_required(VERSION 2.6.0 FATAL_ERROR)
project(geov_c++)

## allows us to write custom modules or modifying existing ones
set(CMAKE_MODULE_PATH "${CMAKE_CURRENT_SOURCE_DIR}/cmake_modules/")


find_package(MOOS REQUIRED)
find_package(Goby REQUIRED)
find_package(ProtobufLocal REQUIRED)

## start adding subdirectories
add_subdirectory(src)
