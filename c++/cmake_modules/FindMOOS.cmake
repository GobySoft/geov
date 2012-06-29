set(MOOS_ROOT_DIR "MOOS_ROOT_DIR-NOTFOUND" CACHE STRING "Path to the root of MOOS, e.g. /home/me/MOOS")

find_library(MOOS_LIBRARY NAMES MOOS
  DOC "The MOOS library"
  PATHS /opt /opt/local ~/ ${CMAKE_SOURCE_DIR}/.. ${MOOS_ROOT_DIR}/..
  PATH_SUFFIXES moos-ivp/MOOS/MOOSBin MOOS/MOOSBin src/moos-ivp/MOOS/MOOSBin src/MOOS/MOOSBin)

get_filename_component(MOOS_LIBRARY_PATH ${MOOS_LIBRARY} PATH)
get_filename_component(MOOS_DIR ${MOOS_LIBRARY_PATH}/../ ABSOLUTE)

# message("MOOS Directory: ${MOOS_DIR}")
# message("MOOS LIBRARY PATH: ${MOOS_LIBRARY_PATH}")

find_library(MOOS_GEN_LIBRARY NAMES MOOSGen
  DOC "The MOOS general library"
  PATHS ${MOOS_LIBRARY_PATH})

find_library(MOOS_UTILITY_LIBRARY NAMES MOOSUtility
  DOC "The MOOS utility library"
  PATHS ${MOOS_LIBRARY_PATH})

find_path(MOOS_CORE_INCLUDE_DIR MOOSLIB/MOOSLib.h 
  PATHS ${MOOS_DIR}
  PATH_SUFFIXES Core)

find_path(MOOS_ESSENTIAL_INCLUDE_DIR MOOSUtilityLib/MOOSGeodesy.h
  PATHS ${MOOS_DIR}
  PATH_SUFFIXES Essentials)

# message("MOOS Core Include DIR: ${MOOS_CORE_INCLUDE_DIR}")
# message("MOOS Essential Include DIR: ${MOOS_ESSENTIAL_INCLUDE_DIR}")
# message("MOOS LIBRARY: ${MOOS_LIBRARY}")
# message("MOOS GEN LIBRARY: ${MOOS_GEN_LIBRARY}")
# message("MOOS UTILITY LIBRARY: ${MOOS_UTILITY_LIBRARY}")

mark_as_advanced(MOOS_CORE_INCLUDE_DIR MOOS_ESSENTIAL_INCLUDE_DIR
  MOOS_LIBRARY MOOS_GEN_LIBRARY MOOS_UTILITY_LIBRARY) 

include(FindPackageHandleStandardArgs)
FIND_PACKAGE_HANDLE_STANDARD_ARGS(MOOS DEFAULT_MSG
  MOOS_DIR MOOS_CORE_INCLUDE_DIR MOOS_ESSENTIAL_INCLUDE_DIR MOOS_GEN_LIBRARY MOOS_UTILITY_LIBRARY)

if(MOOS_FOUND)
  set(MOOS_INCLUDE_DIRS ${MOOS_CORE_INCLUDE_DIR} ${MOOS_ESSENTIAL_INCLUDE_DIR})
  find_package(Threads)
  set(MOOS_LIBRARIES ${MOOS_LIBRARY} ${MOOS_GEN_LIBRARY} ${MOOS_UTILITY_LIBRARY} ${CMAKE_THREAD_LIBS_INIT})
  set(MOOS_ROOT_DIR "${MOOS_DIR}" CACHE STRING "Path to the root of MOOS, e.g. /home/me/MOOS" FORCE)
endif()
