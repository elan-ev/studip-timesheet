// composables/useHolidays.js
import { gettext } from '@/i18n.js';

function getEasterSunday(year) {
    const a = year % 19;
    const b = Math.floor(year / 100);
    const c = year % 100;
    const d = Math.floor(b / 4);
    const e = b % 4;
    const f = Math.floor((b + 8) / 25);
    const g = Math.floor((b - f + 1) / 3);
    const h = (19 * a + b - d - g + 15) % 30;
    const i = Math.floor(c / 4);
    const k = c % 4;
    const l = (32 + 2 * e + 2 * i - h - k) % 7;
    const m = Math.floor((a + 11 * h + 22 * l) / 451);
    const month = Math.floor((h + l - 7 * m + 114) / 31);
    const day = ((h + l - 7 * m + 114) % 31) + 1;

    return new Date(Date.UTC(year, month - 1, day));
}

function addDays(baseDate, days) {
    const result = new Date(baseDate.getTime());
    result.setUTCDate(result.getUTCDate() + days);
    return result;
}

function toIsoDate(date) {
    const year = date.getUTCFullYear();
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const day = String(date.getUTCDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

export function useHolidays() {
    function getHolidaysForYear(year, stateCode = 'NI') {
        const y = Number(year);
        const holidays = new Map();
        if (!y) return holidays;

        const easter = getEasterSunday(y);

        const entries = [
            [`${y}-01-01`, gettext.$gettext('Neujahr')],
            [toIsoDate(addDays(easter, -2)), gettext.$gettext('Karfreitag')],
            [toIsoDate(addDays(easter, 0)), gettext.$gettext('Ostersonntag')],
            [toIsoDate(addDays(easter, 1)), gettext.$gettext('Ostermontag')],
            [`${y}-05-01`, gettext.$gettext('Tag der Arbeit')],
            [toIsoDate(addDays(easter, 39)), gettext.$gettext('Christi Himmelfahrt')],
            [toIsoDate(addDays(easter, 50)), gettext.$gettext('Pfingstmontag')],
            [`${y}-10-03`, gettext.$gettext('Tag der Deutschen Einheit')],
            [`${y}-12-24`, gettext.$gettext('Heiligabend')],
            [`${y}-12-25`, gettext.$gettext('1. Weihnachtstag')],
            [`${y}-12-26`, gettext.$gettext('2. Weihnachtstag')],
            [`${y}-12-31`, gettext.$gettext('Silvester')],
        ];

        const corpusChristiStates = ['BW', 'BY', 'HE', 'NW', 'RP', 'SL'];
        if (corpusChristiStates.includes(stateCode)) {
            entries.push([toIsoDate(addDays(easter, 60)), gettext.$gettext('Fronleichnam')]);
        }

        const allSaintsStates = ['BW', 'BY', 'NW', 'RP', 'SL'];
        if (allSaintsStates.includes(stateCode)) {
            entries.push([`${y}-11-01`, gettext.$gettext('Allerheiligen')]);
        }

        const reformationStates = ['BB', 'HB', 'HH', 'MV', 'NI', 'SN', 'ST', 'SH', 'TH'];
        if (reformationStates.includes(stateCode)) {
            entries.push([`${y}-10-31`, gettext.$gettext('Reformationstag')]);
        }

        if (stateCode === 'SN') {
            const nov23 = new Date(Date.UTC(y, 10, 23));
            const dayOfWeek = nov23.getUTCDay();
            const diffToWednesday = (dayOfWeek + 4) % 7;
            const repentanceDay = addDays(nov23, -(diffToWednesday === 0 ? 7 : diffToWednesday));
            entries.push([toIsoDate(repentanceDay), gettext.$gettext('Buß- und Bettag')]);
        }

        entries.forEach(([iso, name]) => {
            holidays.set(iso, name);
        });

        return holidays;
    }

    return {
        getEasterSunday,
        getHolidaysForYear,
    };
}