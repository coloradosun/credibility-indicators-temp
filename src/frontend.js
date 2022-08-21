// Styles.
import './styles.scss';

/**
 * Attach a logout and refresh listener to all .wp_piano_logout_button classes.
 */
const registerIndicatorUI = () => {

    // Get and loop all indicators.
    const indicators = document.getElementsByClassName( 'credibility-indicators__wrapper' );
    Array.from( indicators ).forEach( ( indicatorWrapper ) => {

        // Find closed and open wrappers.
        const closedIndicators = indicatorWrapper.querySelector( '.credibility-indicators__closed' );
        const openIndicators = indicatorWrapper.querySelector( '.credibility-indicators__open' );

        // When clicked, toggle the display.
        closedIndicators.addEventListener( 'click', ( event ) => {
            event.preventDefault();
            openIndicators.style.display =  'none' === openIndicators.style.display ? 'block' : 'none';
        } );
    } );
}

// Wait until page has loaded fully.
window.addEventListener( 'load', () => registerIndicatorUI() );
