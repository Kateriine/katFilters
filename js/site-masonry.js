/* 
 * Tutorial Javascript Methods
 * @author    Zone
 * @email     hello@thisiszone.com
 * @url       http://thisiszone.com 
 * @copyright Copyright (c) 2012, thisiszone.com. All rights reserved.
 */


var Tutorial;

(function ($) {

  Tutorial = {
    init: function () {

      Tutorial.imgLoaded = false;

      //This is the content container, containing all of the articles
      Tutorial.masonry = $('.masonry');
      
      //Navigation bar
      Tutorial.filters = $('.filters');
      Tutorial.rangesNames = [];
      $('.nstSlider').each(function(i) {
        Tutorial.rangesNames[i]=$(this).attr('data-replace')
      })
      Tutorial.ranges = [];
      for (var i = 0; i < Tutorial.rangesNames.length; i++) {
        Tutorial.ranges[i] = $('.'+Tutorial.rangesNames[i]);
      };
      
      //Pagination div containing the next button
      Tutorial.paginationContainer = $('.infinite-pagenavi-container');
      Tutorial.pagination = $('.infinite-pagenavi');

      Tutorial.initiated = $('.nextpostslink').length;
      
      //Boolean set to true to disable filtering when new content is being loaded
      Tutorial.isScrolling = false;
      Tutorial.animationSpeed = 300;
      Tutorial.changeFilters($('.filters-content'), Tutorial.pagination, Tutorial.ranges);

      
      Tutorial.masonry.imagesLoaded(function(){
        Tutorial.initMasonry();
        Tutorial.initImgLazyLoad($('.masonry__article'));
      });
      
      //if user enters site on a filter url, find the filter in the nav and add an active class.  
      $('.filters li.current-menu-item').addClass('active');
      
      Tutorial.initScroll();        
      Tutorial.checkContentFillsPage(); 

      Tutorial.initListSearch();
 
    },
  
    initMasonry: function() {
      //initialise isotope to work on items of class .masonry__article
      //use masonry layout with a column width of 100
      Tutorial.masonry.isotope({
        itemSelector : '.masonry__article',
        layoutMode: 'masonry',
      });
    },

    initListSearch: function() {
        var options1 = {
            valueNames: [ 'list-js-cp' ]
        };
        var options2 = {
            valueNames: [ 'list-js-town' ]
        };

        var list = new List('list-js-cp-main', options1);
        var list2 = new List('list-js-town-main', options2);
    },
    
    
    initImgLazyLoad: function(newElements) {
  
      var $unveil = $(newElements).find('.unveil');
      imgLoaded = true;
      // Tutorial.initMasonry();

      $unveil.addClass('not-loaded').lazyload({
          effect: 'fadeIn',
          load: function() {
              // Disable trigger on this image
              $(this).removeClass("not-loaded");
              setTimeout(function(){Tutorial.masonry.isotope();}, 1000);
          }
      });
      Tutorial.masonry.isotope('bindResize');
      $('.unveil.not-loaded').trigger('scroll');
    },

    createRangeFilters: function(rangeElement) {

      var round =  {};
      if(rangeElement.data("hour")){
        round =  {
              "1":"10",
          }
      }
      else {
        round =  {
              "1":"10",
              "10":"100"
          }
      }
      rangeElement.nstSlider({
        "rounding": round,
          "left_grip_selector": ".leftGrip",
          "right_grip_selector": ".rightGrip",
          "value_bar_selector": ".bar",
          "highlight": {
              "grip_class": "gripHighlighted",
              "panel_selector": ".highlightPanel"
          },
          "value_changed_callback": function(cause, leftValue, rightValue) {
            var linkRange=rangeElement.attr('data-link'), updateLinkRangeLeft='', updateLinkRangeRight='';

            var rangeType=false;
            if(rangeElement.attr('data-hour')=="true") {
              rangeType=true;
            }

            if(rangeType) {
              if(leftValue > 0) {
                var daysLeft = Math.floor( leftValue / 1440);  
                var hourLeft = Math.floor( leftValue % 1440 / 60);          
                var minutesLeft = leftValue % 60;
                if(daysLeft ===0 ) leftValue = '';              
                else if(daysLeft ===1 ) leftValue = daysLeft + 'jour';
                else leftValue = daysLeft + 'jours';
                if(hourLeft !=0 ) leftValue += hourLeft + 'h';
                if(minutesLeft !=0 ) leftValue += minutesLeft + 'min';
              }
              if(rightValue > 0) {
                var daysRight = Math.floor( rightValue / 1440);  
                var hourRight = Math.floor( rightValue % 1440 / 60);        
                var minutesRight = rightValue % 60;
                if(daysRight ===0 ) rightValue = '';
                else if(daysRight ===1 ) rightValue = daysRight + 'jour';
                else rightValue = daysRight + 'jours';
                if(hourRight !=0 ) rightValue += hourRight + 'h';
                if(minutesRight !=0 ) rightValue += minutesRight + 'min';
              }

            }

              rangeElement.siblings('.leftLabel').text(leftValue);
              rangeElement.siblings('.rightLabel').text(rightValue);           
          },
          "user_mouseup_callback":function(vmin, vmax, left_grip_moved) {
            var dataregMin = rangeElement.attr('data-replace')+'_min';
            var dataregMax = rangeElement.attr('data-replace')+'_max';
            var repMin = new RegExp('(' + dataregMin + '=)[0-9]+','ig');
            var repMax = new RegExp('(' + dataregMax + '=)[0-9]+','ig');

            var linkRange=rangeElement.attr('data-link'), updateLinkRangeLeft='', updateLinkRangeRight='';
 
              updateLinkRangeLeft =linkRange.replace( repMin, '$1'+vmin );

              updateLinkRangeRight = updateLinkRangeLeft.replace( repMax, '$1'+vmax );

             rangeElement.attr('data-link', updateLinkRangeRight);

             //get filtered url
              var $url = updateLinkRangeRight;  
              
              //update url without reloading the page
              if(history.pushState ){
                history.pushState('', document.title,$url);
              }   
          
              //get content from page with url filtered content
              $.get($url, function(data){
                //find the masonry container
                $data = $("<div>" + data + "</div>");
                $data = $data.find(".masonry");
                
                //get the new articles
                var $new =  $data.find('div.masonry__article');
                //remove articles from isotope
                Tutorial.masonry.isotope( 'remove', $('.masonry__article' )); 
                //reset the pagination
                Tutorial.resetScroll($url, $data.find('.infinite-pagenavi')); 
                //add new items to isotope
                Tutorial.masonry.isotope('insert',$new);
                if($new.hasClass('mapped-masonry'))
                  Tutorial.resetMap($new);

                for (var i = 0; i < Tutorial.rangesNames.length; i++) {
                  Tutorial.ranges[i] = $data.find('.'+Tutorial.rangesNames[i]);
                };
                Tutorial.changeFilters($data.find('.filters-content'),$data.find('.infinite-pagenavi'),Tutorial.ranges);

                
                Tutorial.masonry.imagesLoaded(function(){
                  Tutorial.initImgLazyLoad($new);
                });
                 
                //scroll back up to top of page
                //window.scrollTo(0,0);
                
                Tutorial.finishedAppending();
                Tutorial.initListSearch();
              }); 

          }
      });
    },

    changeFilters: function(newFilters, newPagination, ranges) {
      Tutorial.filters.html();
      Tutorial.filters.html(newFilters);
      Tutorial.paginationContainer.html();
      Tutorial.paginationContainer.html(newPagination);
      Tutorial.filters.find('a').bind('click', Tutorial.filter);

      $('.uk-button-slideDown').find('.uk-button').on('click', function(e) {
        $(this).parents('.uk-button-slideDown').toggleClass('uk-open')
      });

      $('.filter--choices').find('.uk-button').on('click', function(e) {
        $('.uk-button-slideDown').removeClass('uk-open')
      });
      $('.uk-button--bool').on('click', function(e) {
        $('.uk-button-slideDown').removeClass('uk-open')
      });
       for (var i = 0; i < Tutorial.rangesNames.length; i++) {
        Tutorial.createRangeFilters(ranges[i]); 
      };
        // Tutorial.createRangeFilters(newRange1); 
        // Tutorial.createRangeFilters(newRange2); 
    },
    
    initScroll:function(){      
      //initialise infinite scroll  
      Tutorial.masonry.infinitescroll({
        navSelector  : '.infinite-pagenavi',    // selector for the pagination container
        nextSelector : '.nextpostslink',  // selector for the NEXT link (to page 2)
        restSelector : '.rest',
        noMoreRestSelector : '.no-more-rest',
        itemSelector : 'div.masonry__article',     // selector for all items you'll retrieve 
        loading: {
          speed:'fast',
          msg: $('<div id="infscr-loading"><img alt="Loading..." src="/wp-content/themes/urbangaming/images/ajax-loader.svg" /><div><span class="loading"></span></div></div>')
        },
        finishedMsg: "No more results",
        behavior: 'twitter',
        errorCallback: function(){Tutorial.isScrolling = false;}
      },
      // append the new items to isotope on the infinitescroll callback function.  
      function( newElements ) {
         //Removed cuz by default we show all filtered elements on map, even the ones hidden by the infinitescroll
        //If you want to show only elements that are realy displayed on the page, uncomment this and in the template, change: add_map($queryMain); with add_map($query);
        // if($( newElements ).hasClass('mapped-masonry'))
        //   Tutorial.addOnMap($( newElements ));
        Tutorial.masonry.isotope( 'appended', $( newElements ), Tutorial.finishedAppending ); 
        imgLoaded = false;
        Tutorial.masonry.imagesLoaded(function(){
          Tutorial.initImgLazyLoad(newElements);
        });
      });
    },
    
    finishedAppending: function(){
      //when the new elements have been appended, this function is called as a callback
      //set isScrolling back to false to enable filtering once more
      Tutorial.isScrolling = false;
      //check that there is enough content on the page for the user to be able to scroll
      Tutorial.checkContentFillsPage();
    },
    
    checkContentFillsPage: function(){
      //if the height of the article container is less than that of the window height, add more content automatically.
      if($('.masonry').height() < $(window).height()){
        Tutorial.masonry.infinitescroll('scroll');
      } 
    }, 
    
    resetScroll:function(newPath, pagination){
      //update infinitescroll with the new (filtered) path

      // TO DEACTIVATE LOAD MORE BUTTON AND REACTIVATE AUTOMATIC INFINITESCROLL:
      // isPaused must be FALSE
      if(Tutorial.initiated != 0) {
        Tutorial.masonry.infinitescroll('update', {     
          path  : [(newPath) + '&paged=',''],    // new path for the paged navigation             
          state: {
            isDuringAjax: false,
            isInvalidPage: false,
            isDestroyed: false,
            isDone: false, // For when it goes all the way through the archive.
            isPaused: true,
            currPage: 1
          },
          behavior: 'twitter',
          loading: {
            msg: $('<div id="infscr-loading"><img alt="Loading..." src="/wp-content/themes/urbangaming/images/ajax-loader.svg" /><div><span class="loading"></span></div></div>')
          }
        });    
        pagination.find('.nextpostslink').on('click', function(e){
          e.preventDefault();
          Tutorial.masonry.infinitescroll('retrieve');
        });        
      }
      else {
        //if the very first display had no pagination and the second display has, we have to reinstanciate everything or the infinitescroll won't work
         pagination.find('.nextpostslink').on('click', function(e){
          e.preventDefault();
          if(Tutorial.initiated == 0) {
            Tutorial.initScroll();  
            Tutorial.initiated = 1;
            Tutorial.masonry.infinitescroll('retrieve'); 
          } 
  
        });
      }
    },

    addMarkers: function(newEl) {
      newEl.each(function(i){
        var el = $(this), 
            lat = el.attr('data-lat'), 
            lng = el.attr('data-lng'), 
            elTitle = newEl.find('.masonry__article__title').html(),
            elIcons = newEl.find('.masonry__article__content').html();
        var myLatlng = new google.maps.LatLng(lat, lng);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            icon: 'http://ug:8888/wp-content/themes/urbangaming/images/markerIcon.svg', 
        });
        var boxText = document.createElement("div");
        //boxText.style.cssText = "box-shadow: 3px 3px 2px 3px rgba(0,0,0,0.2); background: #272625; padding: 11px;";
        boxText.innerHTML = '<div class="uk-h4">'+elTitle+'</div>'+ elIcons;
        var myOptions = {
                    content: boxText
                    ,disableAutoPan: false
                    ,maxWidth: 0
                    ,pixelOffset: new google.maps.Size(-112, 0)
                    ,zIndex: null
                    ,boxStyle: { 
                        background: ""
                        ,opacity: 1
                        ,width: "240px"
                     }
                    ,closeBoxMargin: "5px 5px 2px 2px"
                    ,closeBoxURL: 'http://ug:8888/wp-content/themes/urbangaming/images/close.svg'
                    ,infoBoxClearance: new google.maps.Size(1, 1)
                    ,isHidden: false
                    ,pane: "floatPane"
                    ,enableEventPropagation: false
                };
        var ib = new InfoBox(myOptions);
        var oldiBox;
        google.maps.event.addListener(marker, "click", function (e) {
            if( oldiBox != undefined ){
                oldiBox.close();
            }
            ib.open(map, this);
            map.setCenter(marker.getPosition());
            oldiBox = ib;
        });

        var boxText1 = document.createElement("div");
            //boxText1.style.cssText = "box-shadow: 3px 3px 0 0 rgba(0,0,0,0.12); background: #252525; padding: 11px;";
            boxText1.innerHTML = 
                  '<div class="uk-h4">'+elTitle+'</div>';
        var myOptions = {
              content: boxText1
              ,disableAutoPan: false
              ,maxWidth: 0
              ,pixelOffset: new google.maps.Size(-112, 0)
              ,zIndex: null
              ,boxStyle: { 
                  background: ""
                  ,opacity: 1
                  ,width: "240px"
               }
              ,closeBoxURL: ""
          };
        var iw = new InfoBox(myOptions);
                
        google.maps.event.addListener(marker, 'mouseover', function() {
            iw.open(map, this);
        });

        google.maps.event.addListener(marker, 'mouseout', function() {
            iw.close();
        });

        bounds.extend(myLatlng);
      });

    },


    resetMap: function(newEl) {

      map = new google.maps.Map(document.getElementById("google_map_canvas"), mapOptions);
        // map.mapTypes.set('map_style', styledMap);
        // map.setMapTypeId('map_style');        
      bounds = new google.maps.LatLngBounds();

      Tutorial.addMarkers(newEl);


      if (newEl.length > 1) {
        map.fitBounds(bounds);
      }
      else if (newEl.length == 1) {
        map.setCenter(bounds.getCenter());
        map.setZoom(12);
      }
    },

    addOnMap:function(newEl) {
      Tutorial.addMarkers(newEl);
        map.fitBounds(bounds);
    },

    // filterRangeMin: function (e) {
    //   console.log($(this));

    // },
    // filterRangeMax: function (e) {
    //   console.log($(this));

    // },
    
    filter:function(e){
      
      e.preventDefault();

      var $this = $(this);
      
      if(Tutorial.isScrolling || $this.hasClass('btn')){
        return;
      }

      // don't proceed if already selected
      if ( $this.hasClass('uk-button--bool') )       {
        $this.toggleClass('uk-button--active');
      }
        Tutorial.isScrolling = true;
        //add active class to filter and remove from old active filter.
       
        
        //get filtered url
        var $url = $this.attr('href');	
        
        //update url without reloading the page
        if(history.pushState ){
          history.pushState('', document.title,$url);
        }   
	  
       // get content from page with url filtered content
        $.get($url, function(data){
          //find the masonry container
          $data = $("<div>" + data + "</div>");
          $data = $data.find(".masonry");
          
          //get the new articles
          var $new =  $data.find('div.masonry__article');
          //remove articles from isotope
          Tutorial.masonry.isotope( 'remove', $('.masonry__article' )); 

          //reset the pagination
          Tutorial.resetScroll($url, $data.find('.infinite-pagenavi')); 
          //add new items to isotope
          Tutorial.masonry.isotope('insert',$new);
          if($new.hasClass('mapped-masonry'))
            Tutorial.resetMap($new);
          for (var i = 0; i < Tutorial.rangesNames.length; i++) {
              Tutorial.ranges[i] = $data.find('.'+Tutorial.rangesNames[i]);
            };
          Tutorial.changeFilters($data.find('.filters-content'),$data.find('.infinite-pagenavi'),Tutorial.ranges);
          
          Tutorial.masonry.imagesLoaded(function(){
            Tutorial.initImgLazyLoad($new);
          });
           
          //scroll back up to top of page
          //window.scrollTo(0,0);
          
		      Tutorial.finishedAppending();
          Tutorial.initListSearch();
        }); 
    }
    
  };

  $(function() {

    Tutorial.init();

  });

})(jQuery);