# Brainswarm.Cleanup #

This package provides a command controller that allows you to clean up resources.
Tested with Flow 2.x

### How to use it ###

Add the package to your Flow Installation.
Afterwards you can use the following commands:

./flow resources:cleanup
This loops through all files in the Data/Persistent/Resources/ folder and checks if they are still being referenced in the database. If not, the files are deleted.

./flow resources:cleanupbydb
This loops through all resources that are known in the database and deletes them if they aren't referenced anymore by a model.

### Hints ###

Since this package is manipulating resources and the database it is recommended to make a backup of both your Resources as well as the database.
The package can be very helpful when you need to save disc space or need to cleanup things e.g. when preparing for an upgrade to Flow 3.x and above.
