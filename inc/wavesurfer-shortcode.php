<?php
/**
 * Wavesurfer Shortcode functions
 *
 * @package rslfranchise
 */

// Exit if accessed directly.

defined( 'ABSPATH' ) || exit;

// Wavesurfer implementation
function wavesurfer_shortcode($atts) {
    static $instance = 0;
    $instance++;

    // Extract the audio_url attribute from the shortcode
    $attributes = shortcode_atts(array(
        'audio_url' => ''
    ), $atts);

    $container_id = 'wavesurfer-container-' . $instance;
    $waveform_id = 'waveform-' . $instance;
    $play_id = 'play-' . $instance;
    $time_id = 'time-' . $instance;
    $duration_id = 'duration-' . $instance;
    $zoom_id = 'zoom-' . $instance;

    ob_start();
    ?>

    <div id="<?php echo $container_id; ?>" class="wavesurfer-container dashboard-section">

        <div class="wavesurfer-main">

            <div id="<?php echo $waveform_id; ?>" class="wavesurfer-inner"></div>

            <div class="wavesurfer-time">
                <div id="<?php echo $time_id; ?>">0:00</div>
                <div id="<?php echo $duration_id; ?>">0:00</div>
            </div>

        </div>

        <hr>

        <div class="wavesurfer-controls">

            <button id="<?php echo $play_id; ?>">Play</button>

            <label class="wavesurfer-zoom">
                <div>Zoom: </div><input id="<?php echo $zoom_id; ?>" type="range" min="1" max="1000" value="1" />
            </label>

        </div>

    </div>

    <script type="module">
    import WaveSurfer from 'https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.esm.js'

    if (!window.currentPlaying) {
        window.currentPlaying = null;
    }

    const wavesurfer = WaveSurfer.create({
      container: '#<?php echo $waveform_id; ?>',
      waveColor: '#646b6d',
      progressColor: '#222222',
      cursorColor: "#4cadc2",
      cursorWidth: 2,
      dragToSeek: true,
      url: '<?php echo esc_js($attributes['audio_url']); ?>',
      minPxPerSec: 1,
      barWidth: 2,
      barGap: 1,
      barRadius: 2,
    })

    // Update the zoom level on slider change
    wavesurfer.once('decode', () => {
      const slider = document.querySelector('#<?php echo $zoom_id; ?>')

      slider.addEventListener('input', (e) => {
        const minPxPerSec = e.target.valueAsNumber
        wavesurfer.zoom(minPxPerSec)
      })
    })

    const playButton = document.querySelector('#<?php echo $play_id; ?>')

    playButton.onclick = () => {
      // console.log('Play button clicked for instance:', <?php echo $instance; ?>);
      if (window.currentPlaying) {
        // console.log('Current playing instance:', window.currentPlaying);
      } else {
        // console.log('No current playing instance');
      }

      if (window.currentPlaying && window.currentPlaying !== wavesurfer) {
        // console.log('Pausing current playing instance');
        window.currentPlaying.pause();
      }

      wavesurfer.playPause();

      if (wavesurfer.isPlaying()) {
        // console.log('Setting currentPlaying to this instance:', <?php echo $instance; ?>);
        window.currentPlaying = wavesurfer;
      } else {
        // console.log('Pausing current instance:', <?php echo $instance; ?>);
        window.currentPlaying = null;
      }
    }

    // Handle play and pause events to change button text
    wavesurfer.on('play', () => {
      playButton.textContent = 'Stop';
    });

    wavesurfer.on('pause', () => {
      playButton.textContent = 'Play';
    });

    wavesurfer.on('finish', () => {
      playButton.textContent = 'Play';
      if (window.currentPlaying === wavesurfer) {
        // console.log('Finished playing, resetting currentPlaying');
        window.currentPlaying = null;
      }
    });

    // Current time & duration
    {
      const formatTime = (seconds) => {
        const minutes = Math.floor(seconds / 60)
        const secondsRemainder = Math.round(seconds) % 60
        const paddedSeconds = `0${secondsRemainder}`.slice(-2)
        return `${minutes}:${paddedSeconds}`
      }

      const timeEl = document.querySelector('#<?php echo $time_id; ?>')
      const durationEl = document.querySelector('#<?php echo $duration_id; ?>')
      wavesurfer.on('decode', (duration) => (durationEl.textContent = formatTime(duration)))
      wavesurfer.on('timeupdate', (currentTime) => (timeEl.textContent = formatTime(currentTime)))
    }

    </script>
    
  <?php
  return ob_get_clean();
}

add_shortcode('wavesurfer', 'wavesurfer_shortcode');
?>
