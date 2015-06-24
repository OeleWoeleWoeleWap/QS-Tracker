function run(){
  
    initLabels();

	var campaignSelector = AdWordsApp
     .campaigns()
     .get();
    while(campaignSelector.hasNext()){
      checkScriptQuotas(300,10000,10000);

    	var campaign = campaignSelector.next();
      Logger.log(campaign.getName());
      if(checkLabel(campaign))  continue;
      if(!campaign.isEnabled()) continue;

    	var adGroupSelector = campaign
    	  .adGroups()
    	  .get();

    	while(adGroupSelector.hasNext()){
        checkScriptQuotas(100,100000,100000);


    		var adGroup = adGroupSelector.next();
        if(!adGroup.isEnabled()) continue;
        if(checkLabel(adGroup))  continue;

    		var keywdSelector = adGroup
    		  .keywords()
    		  .get();
    		  
    		while(keywdSelector.hasNext()){
    			var keywd = keywdSelector.next();

          if(!keywd.isEnabled()) continue;

    			var sUrl = "http://hera.clickvalue.nl/live/adwords_scripts/qs_tracker/store_data.php?";
    			sUrl += "client="+encodeURI(AdWordsApp.currentAccount().getName().replace('&','').replace('?','').replace('=',''));
    			sUrl += "&campaign="+encodeURI(campaign.getName().replace('&','').replace('?','').replace('=',''));
    			sUrl += "&adGroup="+encodeURI(adGroup.getName().replace('&','').replace('?','').replace('=',''));
    			sUrl += "&keywd="+encodeURI(keywd.getText().replace('&','').replace('?','').replace('=',''));
				  sUrl += "&qs="+keywd.getQualityScore() * 100;
				  sUrl += "&impressions="+keywd.getStatsFor('LAST_MONTH').getImpressions();
          store(sUrl.replace('#',''));
      	}
    	  adGroup.applyLabel("QS tracker");
    	}
      campaign.applyLabel("QS tracker");
    	
    }
    Logger.log('Script done - all keywords tracked');
}

function store(surl){
  try{
    UrlFetchApp.fetch(surl);
  }catch(e){
    Logger.log(e.message)
    Utilities.sleep(5000);
    store(surl);
  }
}

function initLabels(){
  var date = new Date;
  var label = AdWordsApp.labels()
    .withCondition("Name = 'QS tracker'")
    .get();
  
  if(!label.hasNext()){
    AdWordsApp.createLabel("QS tracker",'tracked');
  }
  else{
    if(date.getDate() == 1){
      label.next().remove();
      AdWordsApp.createLabel("QS tracker",'tracked');
    }
  }

  return true;
}

function checkLabel(object){
  var label = object.labels()
    .withCondition("Name = 'QS tracker'")
    .get();
  if(label.hasNext())
    return true;
  return false;
}

function checkScriptQuotas(iTimeQuota,iCreateQuota,iGetQuota){
    /*
    Checks the status of the script with respect to the parameter quotas

    Parameters:
      @iTimeQuota   : How much time should be remaining to return true;
      @iCreateQuota   : How much create should be remaining to return true;
      @iGetQuota    : How much get should be remaining to return true;

    Returns:
      true:   if all quota's are met
      false:  else;

    */
    var intRemTime = AdWordsApp.getExecutionInfo().getRemainingTime();
    var intRemCreate = AdWordsApp.getExecutionInfo()
      .getRemainingCreateQuota();
    var intRemGet = AdWordsApp.getExecutionInfo().getRemainingGetQuota();

    if(intRemTime < iTimeQuota || intRemCreate < iCreateQuota || 
        intRemGet < iGetQuota)
      throw 'Quota Reached, see you next hour';
    
    return true;
  }


run();