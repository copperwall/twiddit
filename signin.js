var LOGGER = 1
function doSignUp() {

   $.ajax({
      url: "signup",
      data: {username:($(""))},
      type: "POST",
      dataType: "json",
      success: function(json) {
         if (LOGGER) {
            console.log(json["daily"]["summary"])
         }

      },
      error: function(xhr, status, errorThrown) {
         console.log("I Failed :(!")
         alert(errorThrown)
      }
   });
}

function doLogIn() {

   $.ajax({
      url: "",
      data: {},
      type: "GET",
      dataType: "json",
      success: function(json) {
         if (LOGGER) {
            console.log(json["daily"]["summary"])
         }

      },
      error: function(xhr, status, errorThrown) {
         console.log("I Failed :(!")
         alert(errorThrown)
      }
   });
}
