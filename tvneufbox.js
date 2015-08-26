exports.action = function(data, callback, config){

  // Retrieve config
  config = config.modules.tvneufbox;
  if (!config.api_url){
    console.log("Missing Serveur");
    return
  }
  console.log("config.api_url: " + config.api_url);
  // Build URL
  
if (data.action== 'commande')
	{
	var url = config.api_url + '/stb/index.php?cmd='+data.commande;
  callback({'tts': "Je m'en noccupe !"});
	console.log("Sending request to: " + url);
  }
   
if(data.action == 'chaine')
  {
	var url = config.api_url + '/stb/index.php?chaine='+data.chaine;
  callback({'tts': "Je m'en noccupe !"});
	console.log("Sending request to: " + url);
  }
  // Send Request
  var request = require('request');
  request({ 'uri' : url }, function (err, response, body){
    
    if (err || response.statusCode != 200) {
      callback({'tts': "L'action a échoué"});
      return;
    }
	
  });
}
