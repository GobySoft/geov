// t. schneider tes@mit.edu 4.23.08
// laboratory for autonomous marine sensing systems
// massachusetts institute of technology 
// 
// this is iMOOS2SQLMain.cpp 
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
 
int main(int argc, char * argv[])
{
  return goby::moos::run<CiMOOS2SQL>(argc, argv);
}
