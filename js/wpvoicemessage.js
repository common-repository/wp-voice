"use strict";

document.addEventListener('DOMContentLoaded', function() {
    /*document.addEventListener( 'wpcf7invalid', function( event ) {
        // wpcf7invalid wpcf7mailsent
        var form_id = event.detail.id;
        // vmwpmdp-send-rec-btn
        var form = document.getElementById(form_id);
        var send_btn = form.querySelectorAll('.vmwpmdp-send-rec-btn')[0];
        if( send_btn ){
            var cform = form.querySelectorAll('.vmwpmdp-wpvoicemessage-form-box')[0];
            if( cform ){
                var cform_id = cform.getAttribute('cform-id');
                var cform_name = cform.getAttribute('cform-name');
                form.setAttribute("cform-id", cform_id);
                form.setAttribute("cform-name", cform_name);
                sendButtonClick(form);
            }
        }
    }, false);*/

    URL = window.URL || window.webkitURL; // webkitURL is deprecated but nevertheless
    let gumStream; // Stream from getUserMedia()
    let rec; // Recorder.js object
    let input; // MediaStreamAudioSourceNode we'll be recording
    let AudioContext = window.AudioContext || window.webkitAudioContext;
    let audioContext;
    let audioBlob;
    let drawVisual;
    let draw;
    let timerInterval;
    let countdownInterval;
    let isTimerPaused = false;
    let isCountdownPaused = false;
    let form;
    let sampleRate;

    var record_object = {};
        record_object.record_id = [];
        record_object.type='qccf7wpvoicemessage';

    /** Open popup. */
    let fbutton = document.getElementById( 'vmwpmdp-wpvoicemessage-fbutton' );
    if ( null !== fbutton ) {
        fbutton.addEventListener( 'click', openPopup );
    }

    /** Close modal by overlay click. */
    let overlay = document.querySelector( '.vmwpmdp-wpvoicemessage-fbutton-overlay' );
    if ( null !== overlay ) {
        overlay.addEventListener( 'click', closePopup, false );

        /** Close modal by close button click. */
        overlay.querySelectorAll( '.vmwpmdp-wpvoicemessage-fbutton-modal .vmwpmdp-wpvoicemessage-close' )[0].addEventListener( 'click', closePopup, false );
    }

    /**
     * Do not close popup by click on window
     * and stop event propagation to overlay.
     **/
    let popup = document.querySelector( '.vmwpmdp-wpvoicemessage-fbutton-modal' );
    if ( null !== popup ) {
        popup.addEventListener( 'click', ( event ) => { event.stopPropagation(); }, false );
    }

    /** Add Events to buttons. */
    let startRecordButtons = document.querySelectorAll( '.vmwpmdp-wpvoicemessage-form-box .vmwpmdp-wpvoicemessage-start-btn' );
    for ( const recordBtn of startRecordButtons ) {

        /** Current QCvoicemssg Form */
        const cForm = recordBtn.parentNode.parentNode.parentNode; // .vmwpmdp-wpvoicemessage-form-box

        /** Start Recording Button */
        recordBtn.addEventListener( 'click', () => { startRecordingButtonClick( cForm ); } );

        /** Reset button on Speak Now view. */
        const resetBtn = cForm.querySelector( '.vmwpmdp-speak-now-btns .vmwpmdp-reset-rec-btn' );
        resetBtn.addEventListener( 'click', () => { resetButtonClick( cForm ) } );

        /** Stop button on Speak Now view. */
        const stopBtn = cForm.querySelector( '.vmwpmdp-speak-now-btns .vmwpmdp-stop-rec-btn' );
        stopBtn.addEventListener( 'click', () => { stopButtonClick( cForm ); } );

        /** Reset button on Send view. */
        // const resetSendBtn = cForm.querySelector( '.vmwpmdp-send-btns .vmwpmdp-reset-rec-btn' );
        // resetSendBtn.addEventListener( 'click', () => { resetSendButtonClick( cForm ); } );

        /** Start a New Message. */
        const startNewMessageBtn = cForm.querySelector( '.vmwpmdp-wpvoicemessage-thanks-box .vmwpmdp-restart' );
        startNewMessageBtn.addEventListener( 'click', () => { yesResetButtonClick( cForm ) } );

        /** Send button on Send view. */
        if(document.querySelector('.wpcf7') != null){

            if( document.querySelector(".cf7-wpvoicemessage-field") != null){
                var wpcf7btn = document.querySelector( '.wpcf7-submit');
                localStorage.setItem("formcheckup",  0 );
                wpcf7btn.addEventListener('click',function(event){
                    console.log(localStorage.getItem("formcheckup"));
                    if(localStorage.getItem("formcheckup")==0){
                        event.preventDefault();
                        localStorage.setItem("formcheckup",  1);
                    }
        
                    document.getElementsByClassName("vmwpmdp-send-rec-btn")[0].click();
                    
                },false);
            }
        }
        const sendBtn = cForm.querySelector( '.vmwpmdp-send-btns .vmwpmdp-send-rec-btn' );
        sendBtn.addEventListener( 'click', () => { sendButtonClick( cForm ); } );

        /** Yes button on Reset view. */
        const yesResetBtn = cForm.querySelector( '.vmwpmdp-wpvoicemessage-send-box .vmwpmdp-speak-now-btns .vmwpmdp-reset-rec-yes' );
       
        yesResetBtn.addEventListener( 
            'click', () => { yesResetButtonClick( cForm ); } );

        /** No button on Reset view. */
        const noResetBtn = cForm.querySelector( '.vmwpmdp-speak-now-btns .vmwpmdp-reset-rec-no' );
        noResetBtn.addEventListener( 'click', ( e ) => { noResetButtonClick( e.target, cForm ); } );

        /** Disable form submitting, we use AJAX. */
        form = cForm.querySelector( '.vmwpmdp-wpvoicemessage-send-box form' );
        if ( form ) {
            form.addEventListener( 'submit', ( e ) => {
                e.preventDefault();
            } );
        }

    }

    /** Try again button after microphone access error. */
    let micTryAgainBts = document.querySelectorAll( '.vmwpmdp-wpvoicemessage-form-box .vmwpmdp-mic-access-err-reload-btn' );
    for ( const micTryAgainBtn of micTryAgainBts ) {
        micTryAgainBtn.addEventListener( 'click', ()=>{ location.reload(); } );
    }

    


    /**
     * Open Popup.
     **/
    function openPopup() {

        let root = document.getElementsByTagName( 'html' )[0];
        let overlay = document.querySelector( '.vmwpmdp-wpvoicemessage-fbutton-overlay' );
        overlay.style.display = 'block';
        root.classList.add( 'vmwpmdp-wpvoicemessage-modal-opened' );

    }

    /**
     * Close Popup.
     **/
    function closePopup() {
        btn.classList.remove('popup-open');
        let root = document.getElementsByTagName( 'html' )[0];
        let overlay = document.querySelector( '.vmwpmdp-wpvoicemessage-fbutton-overlay' );

        /** Reverse animation */
        root.classList.add( 'vmwpmdp-wpvoicemessage-reverse' );

        /** Hide all */
        setTimeout( function () {

            overlay.style.display = 'none';
            root.classList.remove( 'vmwpmdp-wpvoicemessage-modal-opened' );
            root.classList.remove( 'vmwpmdp-wpvoicemessage-reverse' );

            /** Show Start Recording step. */
            let pop_cForm = document.querySelector( '.vmwpmdp-wpvoicemessage-fbutton-modal .vmwpmdp-wpvoicemessage-form-box' );
            showStartRecordingStep( pop_cForm );

            /** Stop all media stream. */
            stopAllStreams();

        }, 600 );

    }

    /**
     * No button on Reset view.
     **/
    function noResetButtonClick( target, cForm ) {

        if ( 'showSendStep' === target.getAttribute( 'show-step' ) ) {

            /** Show Send view. */
            showSendStep( cForm );
            target.setAttribute( 'show-step', '' );

        } else {

            /** Resume Recording. */
            rec.record();
            log( 'Recording resumed.' );

            /** Resume timer. */
            isTimerPaused = false;

            /** Resume Countdown. */
            isCountdownPaused = false;

            /** Resume animation. */
            drawVisual = window.requestAnimationFrame( draw );

            /** Show Speak Now view. */
            showSpeakNowStep( cForm );
        }

    }

    /**
     * Yes button on Reset view.
     **/
    function yesResetButtonClick( cForm ) {

        /** Stop the recording. */
        rec.stop();

        /** Stop microphone access. */
        gumStream.getAudioTracks()[0].stop();

        /** Show Start Recording step. */
        showStartRecordingStep( cForm );

    }

    /**
     * Send button on Send view.
     **/
    function sendButtonClick( cForm ) {

        fillCurrentURL( cForm );
        fillUserDetails( cForm );

        let formData;

        cForm.classList.add( 'vmwpmdp-busy' ); // Show animation.

        /** Do we have Additional Fields. */
        let form = cForm.querySelector( '.vmwpmdp-wpvoicemessage-send-box form' );
        if ( form ) {

            /** Validate additional fields. */
            if ( ! form.checkValidity() ) {
                form.reportValidity();
                cForm.classList.remove( 'vmwpmdp-busy' ); // Hide animation.
                return;
            }

            /** Get Additional fields data. */
            formData = new FormData( form );

        /** There are no Additional Fields. */
        } else {

            /** Get Additional fields data. */
            formData = new FormData();

        }

        /** Add Audio data. */
        formData.append( 'vmwpmdp-wpvoicemessage-audio', audioBlob );

        /** Add audio Sample Rate. */
        formData.append( 'vmwpmdp-wpvoicemessage-audio-sample-rate', sampleRate );

        /** Add Action to process on WP side. */

        if( cForm.parentNode.classList.contains('wpcf7-form') ){
            formData.append( 'action', 'cf7wpvoicemessage_send' );
        }else{
            formData.append( 'action', 'wpvoicemessage_send' );
        }

        /** Add nonce to security. */
        formData.append( 'nonce', vmwpmdpContacterWP.nonce );

        /** Add cform id. */
        formData.append( 'cform-id', cForm.getAttribute( 'cform-id' ) );

        /** Send Data to WP. */
        let xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {

            if ( xhr.readyState === 4 && xhr.status === 200 ) {

                log( xhr.responseText );

                cForm.classList.remove( 'vmwpmdp-busy' ); // Hide animation.

                /** Show Thanks message. */
               // if( cForm.parentNode.classList.contains('wpcf7-form') ){
                    var response = JSON.parse(xhr.response)
                    if( response.status == 'ok' && response.post_id > 0 ){
                        var url_field = cForm.querySelectorAll('.cf7-wpvoicemessage-field')[0];
                        if( url_field ){
                            var records_ids = record_object.record_id;
                            records_ids.pop();
                            records_ids.push(response.post_id);
                            url_field.setAttribute('value', JSON.stringify(record_object));
                            console.log(JSON.stringify(record_object))
                            setTimeout(() => {
                                if(localStorage.getItem("formcheckup")==1){
                                    document.getElementsByClassName( 'wpcf7-form')[0].submit();
                                    localStorage.setItem("formcheckup",  0);
                                }
                            }, 2000);
                        }
                        
                        showThanksStep( cForm );
                    }else{
                        cForm.classList.remove( 'vmwpmdp-busy' ); // Hide animation.
                        log( 'Network Error.', 'error', true );

                        /** Show Error message. */
                        showErrorStep( cForm );
                    }
                // }else{
                //     showThanksStep( cForm );
                // }
            }

        };

        xhr.onload = function() {
            cForm.classList.remove( 'vmwpmdp-busy' ); // Hide animation.
        };

        xhr.onerror = function() { // only triggers if the request couldn't be made at all
            cForm.classList.remove( 'vmwpmdp-busy' ); // Hide animation.
            log( 'Network Error.', 'error', true );

            /** Show Error message. */
            showErrorStep( cForm );
        };

        cForm.classList.add( 'vmwpmdp-busy' ); // Show animation.
        xhr.open( 'POST', vmwpmdpContacterWP.ajaxurl, true );
        xhr.send( formData );

    }

    /**
     * Fill current location to filed if needed.
     **/
    function fillCurrentURL( cForm ) {

        let vmwpmdpCurrentURL = cForm.querySelectorAll('#vmwpmdpCurrentURL')[0];
        if ( 'undefined' === typeof vmwpmdpCurrentURL ) { return; }

        vmwpmdpCurrentURL.value = window.location.href;

    }

    /**
     * Fill user details to filed if needed.
     **/
    function fillUserDetails( cForm ) {

        let vmwpmdpUserDetails = cForm.querySelectorAll( '#vmwpmdpUserDetails' )[0];
        if ( 'undefined' === typeof vmwpmdpUserDetails ) { return; }

        let login = cForm.querySelectorAll( '.vmwpmdp-wpvoicemessage-additional-fields' )[0].getAttribute( 'user-login' );
        let ip = cForm.querySelectorAll( '.vmwpmdp-wpvoicemessage-additional-fields' )[0].getAttribute( 'user-ip' );

        if ( ! login ) {
            login = 'Guest';
        }

        vmwpmdpUserDetails.value = login + ' (' + ip + ')';

    }

    /**
     * Reset button on Send view.
     **/
    function resetSendButtonClick( cForm ) {

        /** Use attribute show-step as flag for No button. */
        cForm.querySelector( '.vmwpmdp-speak-now-btns .vmwpmdp-reset-rec-no' ).setAttribute( 'show-step', 'showSendStep' );

        /** Show Reset step. */
        showResetStep( cForm );

    }

    /**
     * Stop Button on Speak Now view.
     **/
    function stopButtonClick( cForm ) {

        /** Stop Recording. */
        if ( rec.recording ) { rec.stop(); }
        log( 'Recording stopped.' );

        /** Stop timer. */
        clearInterval( timerInterval );

        /** Stop countdown. */
        clearInterval( countdownInterval );

        /** Stop Animation. */
        window.cancelAnimationFrame( drawVisual );

        /** Show Send your recording step. */
        showSendStep( cForm );

        /** Show Additional Fields. */
        showAdditionalFields( cForm );

        /** Stop microphone access. */
        gumStream.getAudioTracks()[0].stop();

        /** Create the wav blob and pass it to createPayer. */
        rec.exportWAV( ( blob ) => { createPayer( blob, cForm ) } );

    }

    /**
     * Reset Button on Speak Now view.
     **/
    function resetButtonClick( cForm ) {

        /** Pause Recording. */
        if ( rec.recording ) { rec.stop(); }
        log( 'Recording paused.' );

        /** Pause timer. */
        isTimerPaused = true;

        /** Pause Countdown. */
        isCountdownPaused = true;

        /** Pause animation. */
        window.cancelAnimationFrame( drawVisual );

        /** Show Reset step. */
        showResetStep( cForm );

    }

    /**
     * Launches the promise based getUserMedia() and on success it passes the audio stream
     * to an AudioContext which is then passed to our Recorder.js object. The actual recording process
     * is triggered by rec.record().
     **/
    function startRecordingButtonClick( cForm ) {

        /** Show Allow Access to microphone view. */
        showAllowAccessStep( cForm );

        /**
         * We're using the standard promise based getUserMedia()
         * @see: https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
         * @see: https://addpipe.com/blog/audio-constraints-getusermedia/
         **/
        navigator.mediaDevices.getUserMedia( { audio: true, video: false } ).then( function( stream ) {

            log( 'getUserMedia() success. Stream created. Initializing Recorder.' );

            /**
             * Create an audio context after getUserMedia is called.
             * SampleRate might change after getUserMedia is called, like it does on macOS when recording through AirPods
             * the sampleRate defaults to the one set in your OS for your playback device.
             **/
            audioContext = new AudioContext();

            sampleRate = audioContext.sampleRate;
            /** Log the format. */
            log( 'Format: 1 channel pcm @ ' + sampleRate/1000 + 'kHz' );


            /** Assign to gumStream for later use.  */
            gumStream = stream;

            /** Use the stream. */
            input = audioContext.createMediaStreamSource( stream );

            /**
             * Create the Recorder object and configure to record mono sound (1 channel)
             * Recording 2 channels  will double the file size.
             **/
            rec = new Recorder( input,{ numChannels: 1 } );

            /** Start the recording. */
            rec.record();
            log( 'Recording started.' );

            /** Show Speak Now view. */
            showSpeakNowStep( cForm );

            /** Create Timer. */
            createTimer( cForm );

            /** Create Countdown. */
            createCountdown( cForm );

            /** Create Animation. */
            createAnimation( cForm );

        } ).catch( function( err ) {

            /** Show Error if getUserMedia() fails. */
            log( 'Error getUserMedia() fails. See details below.', 'warn', true );
            log( err, 'error', true );

            /** Show Microphone access error step if getUserMedia() fails. */
            showMicrophoneAccessErrStep( cForm )

        } );

    }

    /**
     * Stop all media stream.
     **/
    function stopAllStreams() {

        if ( typeof gumStream !== 'undefined' ) {
            gumStream.getTracks().forEach( function( track) {
                track.stop();
            } );
        }

    }

    /**
     * Create Animation.
     **/
    function createAnimation( cForm ) {

        /**
         * Create Analyser to extract data from audio source.
         * The AnalyserNode interface represents a node able to provide real-time frequency and time-domain analysis information.
         **/
        let analyser = audioContext.createAnalyser();

        /** Connect analyser to audio source. */
        input.connect( analyser );

        /** Array to receive the data from audio source. */
        analyser.fftSize = 2048;
        let bufferLength = analyser.frequencyBinCount;
        let dataArray = new Uint8Array( bufferLength );

        /** Canvas for animation. */
        let animation = cForm.querySelector( '.vmwpmdp-wpvoicemessage-recording-animation canvas' );

        let animationCtx = animation.getContext( "2d" );

        /** Clear the canvas. */
        animationCtx.clearRect( 0, 0, animation.width, animation.height );

        draw = function() {

            /** Using requestAnimationFrame() to keep looping the drawing function once it has been started. */
            drawVisual = requestAnimationFrame( draw );

            /** Grab the time domain data and copy it into our array. */
            analyser.getByteTimeDomainData( dataArray );

            /** Fill the canvas with a solid colour to start. */
            animationCtx.clearRect( 0, 0, animation.width, animation.height ); // Clear the canvas.
            animationCtx.fillStyle = 'rgba( 255, 255, 255, 0.01 )'; // Almost transparent
            animationCtx.fillRect( 0, 0, animation.width, animation.height );

            /** Set a line width and stroke colour for the wave we will draw, then begin drawing a path. */
            animationCtx.lineWidth = 2;

            let startColor = vmwpmdpContacterWP.accentColor.replace(/[^,]+(?=\))/, '0');
            let endColor = vmwpmdpContacterWP.accentColor.replace(/[^,]+(?=\))/, '1');

            const gradient = animationCtx.createLinearGradient(0, 0, 384, 0);
            gradient.addColorStop( 0, startColor );
            gradient.addColorStop( .25 , endColor );
            gradient.addColorStop( .75 , endColor );
            gradient.addColorStop( 1, startColor );
            animationCtx.strokeStyle = gradient;

            animationCtx.beginPath();

            /**
             * Determine the width of each segment of the line to be drawn
             * by dividing the canvas width by the array length (equal to the FrequencyBinCount, as defined earlier on),
             * then define an x variable to define the position to move to for drawing each segment of the line.
             **/
            let sliceWidth = animation.width * 1.0 / bufferLength;
            let x = 0;

            /**
             * Run through a loop, defining the position of a small segment of the wave
             * for each point in the buffer at a certain height based on the data point value form the array,
             * then moving the line across to the place where the next wave segment should be drawn.
             **/
            for ( let i = 0; i < bufferLength; i++ ) {

                let v = dataArray[i] / 128.0;
                let y = v * animation.height/2;

                if ( i === 0 ) {
                    animationCtx.moveTo( x, y );
                } else {
                    animationCtx.lineTo( x, y );
                }

                x += sliceWidth;
            }

            /**
             * Finish the line in the middle of the right hand side of the canvas,
             * then draw the stroke we've defined.
             **/
            animationCtx.lineTo( animation.width, animation.height/2 );
            animationCtx.stroke();
        };

        /** Call the draw() function to start off the whole process. */
        draw();

    }

    /**
     * Create Countdown.
     **/
    function createCountdown( cForm ) {

        const countdownElement = cForm.querySelector( '.vmwpmdp-wpvoicemessage-recording-countdown' );

        if ( null === countdownElement ) { return; }

        let maxDuration = parseInt( cForm.getAttribute( 'max-duration' ) );

        if ( maxDuration === 0 ) { return; } // If maxDuration set to unlimited, countdown is impossible.

        let countdown = maxDuration;

        /** Reset previously countdowns. */
        clearInterval( countdownInterval );
        isCountdownPaused = false;
        let resetMinutes = Math.floor( maxDuration / 60 );
        let resetSeconds = maxDuration - resetMinutes * 60;
        countdownElement.innerHTML = resetMinutes.pad( 2 ) + ':' + resetSeconds.pad( 2 );

        /** Start new countdown. */
        countdownInterval = setInterval( function () {

            if ( isCountdownPaused ) { return; } // Pause.

            countdown--;

            /** If timer lower than 0 Stop recording. */
            if ( maxDuration !== 0 && countdown < 0 ) {
                cForm.querySelector( '.vmwpmdp-speak-now-btns .vmwpmdp-stop-rec-btn' ).click();
            }

            let minutes = Math.floor( countdown / 60 );
            let seconds = countdown - minutes * 60;

            countdownElement.innerHTML = minutes.pad( 2 ) + ':' + seconds.pad( 2 );

        }, 1000 );

    }

    /**
     * Create Timer.
     **/
    function createTimer( cForm ) {
        let timer = 0;
        const timerElement = cForm.querySelector( '.vmwpmdp-wpvoicemessage-recording-timer' );

        if ( null === timerElement ) { return; }

        let maxDuration = parseInt( cForm.getAttribute( 'max-duration' ) );

        /** Reset previously timers. */
        clearInterval( timerInterval );
        isTimerPaused = false;
        timerElement.innerHTML = '00:00';

        /** Start new timer. */
        timerInterval = setInterval( function () {

            if ( isTimerPaused ) { return; } // Pause.

            timer++;

            /** If timer bigger than max-duration Stop recording. */
            if ( maxDuration !== 0 && timer > maxDuration ) {
                cForm.querySelector( '.vmwpmdp-speak-now-btns .vmwpmdp-stop-rec-btn' ).click();
            }

            let minutes = Math.floor( timer / 60 );
            let seconds = timer - minutes * 60;

            timerElement.innerHTML = minutes.pad( 2 ) + ':' + seconds.pad( 2 );

        }, 1000 );
    }

    /**
     * Get recorded audio create player.
     **/
    function createPayer( blob, cForm ) {

        let url = URL.createObjectURL( blob );
        audioBlob = blob;
        let audioEl = document.createElement( 'audio' );
        audioEl.src = url;

        /** Add audio element to the player box. */
        const playerBox = cForm.querySelector( '.vmwpmdp-wpvoicemessage-send-box .vmwpmdp-wpvoicemessage-player-box' );
        playerBox.innerHTML = '';
        playerBox.appendChild( audioEl );

        /** Init Green Audio Player. */
        new GreenAudioPlayer( playerBox );

        /** Set Download Link if we have one. */
        let downloadBtn = cForm.querySelector( '.vmwpmdp-wpvoicemessage-download-link-box > a' );

        if ( null !== downloadBtn ) {
            downloadBtn.setAttribute( 'href', url );
            downloadBtn.setAttribute( 'download', 'record.wav' );
        }

    }

    /**
     * Show Thank you view (.vmwpmdp-wpvoicemessage-thanks-box).
     **/
    function showThanksStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-thanks' ); // Show Thank you step.
    }

    /**
     * Show Error view (.vmwpmdp-wpvoicemessage-error-box).
     **/
    function showErrorStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-error' ); // Show Error step.
    }

    /**
     * Show Send view (.vmwpmdp-wpvoicemessage-send-box).
     **/
    function showSendStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-send' ); // Show Send step.
    }

    /**
     * Show Start Recording view (.vmwpmdp-wpvoicemessage-start-box).
     **/
    function showStartRecordingStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-start' ); // Show Start Recording step.
    }

    /**
     * Show Allow Access to microphone view (.vmwpmdp-wpvoicemessage-allow-access-box).
     **/
    function showAllowAccessStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-allow-access' ); // Show Allow Access step.
    }

    /**
     * Show Reset view (.vmwpmdp-wpvoicemessage-reset-box).
     **/
    function showResetStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-reset' ); // Show Reset step.
    }

    /**
     * Show Microphone access error view (.vmwpmdp-wpvoicemessage-mic-access-err-box).
     **/
    function showMicrophoneAccessErrStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-mic-access-err' ); // Show Microphone access error step.
    }

    /**
     * Show Speak Now view (.vmwpmdp-wpvoicemessage-recording-box).
     **/
    function showSpeakNowStep( cForm ) {
        hideAllViews( cForm ); // Hide all steps.
        cForm.classList.add( 'vmwpmdp-step-recording' ); // Show Recording step.
    }

    /** Show Additional Fields from additional-fields attribute. */
    function showAdditionalFields( cForm ) {

        const additionalFieldsBox = cForm.querySelector( '.vmwpmdp-wpvoicemessage-send-box .vmwpmdp-wpvoicemessage-additional-fields' );

        /** Exit if we don't have Additional Fields. */
        if ( ! additionalFieldsBox ) { return; }

        let additionalFields = additionalFieldsBox.getAttribute( 'additional-fields' );

        additionalFieldsBox.innerHTML = atobUTF8( additionalFields );

    }

    /**
     * Hide all step boxes, by removing all step classes from element.
     *
     * @param {object} el - Current QCvoicemssg Form (.vmwpmdp-wpvoicemessage-form-box)
     **/
    function hideAllViews( el ) {

        el.classList.remove( 'vmwpmdp-step-start' );
        el.classList.remove( 'vmwpmdp-step-allow-access' );
        el.classList.remove( 'vmwpmdp-step-mic-access-err' );
        el.classList.remove( 'vmwpmdp-step-recording' );
        el.classList.remove( 'vmwpmdp-step-reset' );
        el.classList.remove( 'vmwpmdp-step-error' );
        el.classList.remove( 'vmwpmdp-step-send' );
        el.classList.remove( 'vmwpmdp-step-thanks' );

    }

    /**
     * Parse and return current URL parameters.
     **/
    function getUrlVars() {
        let vars = {};
        window.location.href.replace( /[?&]+([^=&]+)=([^&]*)/gi, function( m, key, value ) {
            vars[key] = value;
        } );

        return vars;
    }

    /**
     * Show log message in console if debug is enabled.
     *
     * @param {string} msg - Message to log in console. Required.
     * @param {string} level - Log level: log, warn, error. Optional.
     * @param {boolean} always - True to always enabled message. Optional.
     *
     * @return {void}
     **/
    function log( msg, level = 'log', always = false ) {

        /** Enable debug by URL. */
        let debug = getUrlVars()['debug'];
        debug = typeof debug !== 'undefined';

        /** Enable debug by second params. */
        if ( always ) {
            debug = true;
        }

        // debug = true; // Uncomment this line to enable logging manually.

        /** Exit if debug is disabled. */
        if ( ! debug ) { return; }

        /** Add plugin name to identify messages. */
        msg = 'QCvoicemssg: ' + msg;

        if ( 'log' === level ) {
            console.log( msg );

        } else if ( 'warn' === level ) {
            console.warn( msg );

        } else if ( 'error' === level ) {
            console.error( msg );
        }

    }

    /**
     * Output numbers with leading zeros.
     *
     * @param {number} size - A number of digits.
     **/
    Number.prototype.pad = function( size ) {

        let s = String( this );

        while ( s.length < ( size || 2 ) ) { s = '0' + s; }

        return s;

    }

    /** Return random RGBA color. */
    function randomRGBA() {
        let o = Math.round, r = Math.random, s = 255;
        return 'rgba(' + o( r()*s ) + ',' + o( r()*s ) + ',' + o( r()*s ) + ',' + r().toFixed( 1 ) + ')';
    }



} );

/**
 * The most standard, most cross-browser, most compact,
 * and fastest possible btoa and atob solution for unicode strings with high code points.
 *
 * @see: https://github.com/anonyco/BestBase64EncoderDecoder
 **/
(function(window){
    "use strict";
    var log = Math.log;
    var LN2 = Math.LN2;
    var clz32 = Math.clz32 || function(x) {return 31 - log(x >>> 0) / LN2 | 0};
    var fromCharCode = String.fromCharCode;
    var originalAtob = atob;
    var originalBtoa = btoa;
    function btoaReplacer(nonAsciiChars){
        // make the UTF string into a binary UTF-8 encoded string
        var point = nonAsciiChars.charCodeAt(0);
        if (point >= 0xD800 && point <= 0xDBFF) {
            var nextcode = nonAsciiChars.charCodeAt(1);
            if (nextcode !== nextcode) // NaN because string is 1 code point long
                return fromCharCode(0xef/*11101111*/, 0xbf/*10111111*/, 0xbd/*10111101*/);
            if (nextcode >= 0xDC00 && nextcode <= 0xDFFF) {
                point = (point - 0xD800) * 0x400 + nextcode - 0xDC00 + 0x10000;
                if (point > 0xffff)
                    return fromCharCode(
                        (0x1e/*0b11110*/<<3) | (point>>>18),
                        (0x2/*0b10*/<<6) | ((point>>>12)&0x3f/*0b00111111*/),
                        (0x2/*0b10*/<<6) | ((point>>>6)&0x3f/*0b00111111*/),
                        (0x2/*0b10*/<<6) | (point&0x3f/*0b00111111*/)
                    );
            } else return fromCharCode(0xef, 0xbf, 0xbd);
        }
        if (point <= 0x007f) return nonAsciiChars;
        else if (point <= 0x07ff) {
            return fromCharCode((0x6<<5)|(point>>>6), (0x2<<6)|(point&0x3f));
        } else return fromCharCode(
            (0xe/*0b1110*/<<4) | (point>>>12),
            (0x2/*0b10*/<<6) | ((point>>>6)&0x3f/*0b00111111*/),
            (0x2/*0b10*/<<6) | (point&0x3f/*0b00111111*/)
        );
    }
    window["btoaUTF8"] = function(inputString, BOMit){
        return originalBtoa((BOMit ? "\xEF\xBB\xBF" : "") + inputString.replace(
            /[\x80-\uD7ff\uDC00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]?/g, btoaReplacer
        ));
    }
    //////////////////////////////////////////////////////////////////////////////////////
    function atobReplacer(encoded){
        var codePoint = encoded.charCodeAt(0) << 24;
        var leadingOnes = clz32(~codePoint);
        var endPos = 0, stringLen = encoded.length;
        var result = "";
        if (leadingOnes < 5 && stringLen >= leadingOnes) {
            codePoint = (codePoint<<leadingOnes)>>>(24+leadingOnes);
            for (endPos = 1; endPos < leadingOnes; ++endPos)
                codePoint = (codePoint<<6) | (encoded.charCodeAt(endPos)&0x3f/*0b00111111*/);
            if (codePoint <= 0xFFFF) { // BMP code point
                result += fromCharCode(codePoint);
            } else if (codePoint <= 0x10FFFF) {
                // https://mathiasbynens.be/notes/javascript-encoding#surrogate-formulae
                codePoint -= 0x10000;
                result += fromCharCode(
                    (codePoint >> 10) + 0xD800,  // highSurrogate
                    (codePoint & 0x3ff) + 0xDC00 // lowSurrogate
                );
            } else endPos = 0; // to fill it in with INVALIDs
        }
        for (; endPos < stringLen; ++endPos) result += "\ufffd"; // replacement character
        return result;
    }
    window["atobUTF8"] = function(inputString, keepBOM){
        if (!keepBOM && inputString.substring(0,3) === "\xEF\xBB\xBF")
            inputString = inputString.substring(3); // eradicate UTF-8 BOM
        // 0xc0 => 0b11000000; 0xff => 0b11111111; 0xc0-0xff => 0b11xxxxxx
        // 0x80 => 0b10000000; 0xbf => 0b10111111; 0x80-0xbf => 0b10xxxxxx
        return originalAtob(inputString).replace(/[\xc0-\xff][\x80-\xbf]*/g, atobReplacer);
    };
})(typeof global == "" + void 0 ? typeof self == "" + void 0 ? this : self : global);

/*jQuery(window).on('load', function(){
    jQuery(document).on('click', '.wpbd_voice_message', function(){
        jQuery('#vmwpmdp-wpvoicemessage-fbutton').trigger('click');
    });
});*/