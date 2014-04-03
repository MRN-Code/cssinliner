var juice = require('juice');
var fs = require('fs');
var inputFilename = process.argv[2];
var outputFilename = inputFile + '.inline';
juice(inputFilename, function(err, html) {
    if(!err) {
        fs.writeFile(outputFilename, html);
	console.log(outputFilename);
    } else {
        throw err;
    }
});
