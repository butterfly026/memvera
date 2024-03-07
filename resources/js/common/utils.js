export const getCurrentDateString = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1; // Months start at 0!
    let dd = today.getDate();

    // Add leading zeros to month and day if they are less than 10
    mm = mm < 10 ? '0' + mm : mm;
    dd = dd < 10 ? '0' + dd : dd;

    const formattedToday = `${yyyy}-${mm}-${dd}`;
    return formattedToday;
};

export const getEndMonthDateString = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = today.getMonth() + 1; // Months start at 0 in JavaScript
    const lastDay = new Date(yyyy, mm, 0).getDate(); // 0th day of the next month is the last day of the current month

    const formattedLastDay = `${yyyy}-${mm < 10 ? '0' + mm : mm}-${lastDay < 10 ? '0' + lastDay : lastDay}`;    
    return formattedToday;
};
