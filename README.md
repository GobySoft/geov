# Installing GEOV

These instructions work on an Ubuntu 22.04 machine (virtual [preferred] or real):

```
cd geov/scripts
./geov_mirror
```

(warning if you already have MySQL installed this will overwrite your root password).

After this completes, you should reboot and open a web browser to http://localhost/geov.

## Getting vehicle data into GEOV

You can get vehicle data into GEOV using `goby_geov_interface`. For an example minimal configuration file, see https://github.com/GobySoft/goby3-course/blob/master/launch/alpha/topside_config/goby_geov_interface.pb.cfg.

## Configuring Google Earth

Download Google Earth Pro (Desktop) for your system: https://www.google.com/earth/about/versions/#download-pro

Download the GEOV KML for your local GEOV instance: http://localhost/geov/dl_kml.php

Open the resulting geov_core_*kml file in Google Earth (File->Open).

Once you configure a profile at http://localhost/geov/profile.php, check "bind to this machine's ip" and hit "apply", and click "show simulation" (if applicable) you should see vehicle data.

## Additional Help

Some (currently out-of-date) instructions are bundled with GEOV, and can be viewed at https://gobysoft.org/geov/help.php