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

### License ###

The MIT License (MIT)

Copyright (c) <2016>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
