///////////////////////////////////////
// Helper Functions

function wFix(obj){
	
	$(obj).widowFix({
		prevLimit: 5,
		linkFix: true
	});
}

function countWords(s){

    s = s.replace(/(^\s*)|(\s*$)/gi,"");
    s = s.replace(/[ ]{2,}/gi," ");
    s = s.replace(/\n /,"\n");
    return s.split(" ").length;
}

// Check Uppercase
String.prototype.isUpperCase = function() {

    return this.valueOf().toUpperCase() === this.valueOf();
};

// Check Email
function validateEmail(email) {

	var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	return re.test(email);
}

function newGUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
        return v.toString(16);
    });
}