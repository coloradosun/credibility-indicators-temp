// Styles.
import './styles.scss';

console.log( 'hello world A' );


/**
 * Attach a logout and refresh listener to all .wp_piano_logout_button classes.
 */
const registerIndicatorUI = () => {
    const indicators = document.getElementsByClassName( 'credibility-indicators__wrapper' );
    // console.log( indicatorsClosedUI );
    Array.from( indicators ).forEach( ( indicatorWrapper ) => {


        const closedIndicators = indicatorWrapper.querySelector( '.credibility-indicators__closed' );
        const openIndicators = indicatorWrapper.querySelector( '.credibility-indicators__open' );

        closedIndicators.addEventListener( 'click', ( event ) => {
            // console.log( event );
            event.preventDefault();

            openIndicators.style.display =  'none' === openIndicators.style.display ? 'block' : 'none';
        } );
    } );
}

// Wait until page has loaded fully.
window.addEventListener( 'load', () => registerIndicatorUI() );
