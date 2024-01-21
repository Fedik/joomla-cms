/**
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-extraneous-dependencies
import flatpickr from 'flatpickr';

// Prepare translations
const t = Joomla.Text._;
const locale = {
  firstDayOfWeek: 1,
  weekdays: {
    shorthand: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'].map((s) => t(s)),
    longhand: ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'].map((s) => t(s)),
  },
  months: {
    shorthand: ['JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT',
      'MAY_SHORT', 'JUNE_SHORT', 'JULY_SHORT', 'AUGUST_SHORT',
      'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT'].map((s) => t(s)),
    longhand: ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
      'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'].map((s) => t(s)),
  },
  amPM: [t('JLIB_HTML_BEHAVIOR_AM'), t('JLIB_HTML_BEHAVIOR_PM')],
  weekAbbreviation: t('JLIB_HTML_BEHAVIOR_WK'),
};

/**
 * Create the calendar
 * @param {HTMLInputElement} input
 */
const setUpInput = (input) => {
  const config = input.dataset.calendarField ? JSON.parse(input.dataset.calendarField) : {};
  locale.firstDayOfWeek = config.firstDay || 1;

  flatpickr(input, {
    enableTime: config.enableTime,
    time_24hr: config.time_24hr,
    weekNumbers: config.weekNumbers,
    // minDate: input.getAttribute('min') || null,
    // maxDate: input.getAttribute('max') || null,
    // These are fixed
    dateFormat: 'Y-m-dTH:i',
    altInput: true,
    wrap: true,
    locale,
  });
};

/**
 * Look for all calendar fields
 * @param {HTMLElement} container
 */
const setUpAll = (container) => {
  container.querySelectorAll('[data-calendar-field]').forEach((input) => setUpInput(input));
};

setUpAll(document);
document.addEventListener('joomla:updated', ({ target }) => setUpAll(target));
