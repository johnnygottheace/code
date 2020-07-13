<?php 
$player_visible = "display:none";
$comment = get_comment();
$voice = "";
if($comment){
    
    $voice = get_field('voice', $comment);
    if($voice){
      $player_visible = "";
    }
}

?>

<section class="voice" id="voicecontrol">
  <small onclick="jQuery('#allow').show()" style="cursor:pointer">🎤</small><a class="button" id="r"  onmousedown="startRecording(this);" ontouchstart="startRecording(this);" onmouseup="stopRecording(this)" ontouchend="stopRecording(this)"><?php _e("Keep pressing to record"); ?></a>
  <!-- <button onclick="stopRecording(this);" disabled>stop</button> -->
  <a class="button" style="display:none" id="allow" onclick="init();"><?php _e("Allow recording"); ?></a>
  <audio controls id="voiceplayer" style="<?php echo $player_visible; ?>">
          <source src="<?php echo $voice; ?>" type="audio/wav">
          Your browser does not support the audio element.
          </audio>

  <div class="logs">
  <h2>Recordings</h2>
  <ul id="recordingslist"></ul>
  <h2>Log</h2>
  <pre id="log"></pre>
</div>
  
</section>
<script>

jQuery(document).ready(function($){

  

});

function __log(e, data) {
  log.innerHTML += "\n" + e + " " + (data || '');
}

var audio_context;
var recorder;

function startUserMedia(stream) {
  var input = audio_context.createMediaStreamSource(stream);
  __log('Media stream created.');

  // Uncomment if you want the audio to feedback directly
  //input.connect(audio_context.destination);
  //__log('Input connected to audio context destination.');
  
  recorder = new Recorder(input);
  __log('Recorder initialised.');
}

function startRecording(button) {

  jQuery("#r").addClass("Rec");

  recorder && recorder.record();
  // button.disabled = true;
  button.nextElementSibling.disabled = false;
  __log('Recording...');
}

function stopRecording(button) {
  
  jQuery("#r").removeClass("Rec");

  recorder && recorder.stop();
  // button.disabled = true;
  button.previousElementSibling.disabled = false;
  __log('Stopped recording.');
  
  // create WAV download link using audio data blob
  // createDownloadLink();

  uploadToWordpress();
  
}

function uploadToWordpress(){
  recorder && recorder.exportWAV(function(blob) {
  var formData = new FormData();
      formData.append('audio', blob);
      // formData.append('updoc', $('#user-file')[0].files[0]);
      formData.append('action', "save_audio");
      formData.append('filename', new Date().toISOString() + '.wav');
      // console.log("ajaxurl",ajaxurl);
      jQuery.ajax({
        url: my_ajax_object.ajaxurl,
        type: "POST",
        data:formData,cache: false,
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success:function(data) {
          jQuery("#acf-field_5ec1adc487c27").val(data);
          jQuery("#voiceplayer").attr("src", data);
          jQuery("#voiceplayer").show();
          jQuery("#comment").val(jQuery("#comment").val() + "Audio recorded");
          recorder.clear();
        },
      });
    });
}

function createDownloadLink() {
  recorder && recorder.exportWAV(function(blob) {
    var url = URL.createObjectURL(blob);
    var li = document.createElement('li');
    var au = document.createElement('audio');
    var hf = document.createElement('a');
    
    au.controls = true;
    au.src = url;
    hf.href = url;
    hf.download = new Date().toISOString() + '.wav';
    hf.innerHTML = hf.download;
    li.appendChild(au);
    li.appendChild(hf);
    recordingslist.appendChild(li);
  });
}

function init() {
  jQuery("#r").addClass("shown");
  jQuery("#allow").remove();
  try {
    // webkit shim
    window.AudioContext = window.AudioContext || window.webkitAudioContext;
    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia;
    window.URL = window.URL || window.webkitURL;
    
    audio_context = new AudioContext;
    __log('Audio context set up.');
    __log('navigator.getUserMedia ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
  } catch (e) {
    alert('No web audio support in this browser!');
  }
  
  navigator.getUserMedia({audio: true}, startUserMedia, function(e) {
    __log('No live audio input: ' + e);
  });
};
</script>