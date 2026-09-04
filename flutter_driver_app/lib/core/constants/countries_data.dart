class Country {
  final String name;
  final String code;
  final String dial;
  final String flag;

  const Country({
    required this.name,
    required this.code,
    required this.dial,
    required this.flag,
  });
}

class CountriesData {
  static const Country defaultCountry = Country(
    name: 'United States',
    code: 'US',
    dial: '+1',
    flag: '🇺🇸',
  );

  static const List<Country> allCountries = [
    Country(name: 'United States', code: 'US', dial: '+1', flag: '🇺🇸'),
    Country(name: 'South Africa', code: 'ZA', dial: '+27', flag: '🇿🇦'),
    Country(name: 'Ghana', code: 'GH', dial: '+233', flag: '🇬🇭'),
    Country(name: 'United Kingdom', code: 'GB', dial: '+44', flag: '🇬🇧'),
    Country(name: 'Canada', code: 'CA', dial: '+1', flag: '🇨🇦'),
    Country(name: 'Nigeria', code: 'NG', dial: '+234', flag: '🇳🇬'),
    Country(name: 'India', code: 'IN', dial: '+91', flag: '🇮🇳'),
    Country(name: 'Kenya', code: 'KE', dial: '+254', flag: '🇰🇪'),
    Country(name: 'United Arab Emirates', code: 'AE', dial: '+971', flag: '🇦🇪'),
    Country(name: 'Australia', code: 'AU', dial: '+61', flag: '🇦🇺'),
    Country(name: 'Germany', code: 'DE', dial: '+49', flag: '🇩🇪'),
    Country(name: 'France', code: 'FR', dial: '+33', flag: '🇫🇷'),
    Country(name: 'Saudi Arabia', code: 'SA', dial: '+966', flag: '🇸🇦'),
    Country(name: 'Pakistan', code: 'PK', dial: '+92', flag: '🇵🇰'),
    Country(name: 'Bangladesh', code: 'BD', dial: '+880', flag: '🇧🇩'),
    Country(name: 'Philippines', code: 'PH', dial: '+63', flag: '🇵🇭'),
    Country(name: 'Singapore', code: 'SG', dial: '+65', flag: '🇸🇬'),
    Country(name: 'Malaysia', code: 'MY', dial: '+60', flag: '🇲🇾'),
    Country(name: 'New Zealand', code: 'NZ', dial: '+64', flag: '🇳🇿'),
    Country(name: 'Ireland', code: 'IE', dial: '+353', flag: '🇮🇪'),
    Country(name: 'Spain', code: 'ES', dial: '+34', flag: '🇪🇸'),
    Country(name: 'Italy', code: 'IT', dial: '+39', flag: '🇮🇹'),
    Country(name: 'Netherlands', code: 'NL', dial: '+31', flag: '🇳🇱'),
    Country(name: 'Brazil', code: 'BR', dial: '+55', flag: '🇧🇷'),
    Country(name: 'Mexico', code: 'MX', dial: '+52', flag: '🇲🇽'),
    Country(name: 'Japan', code: 'JP', dial: '+81', flag: '🇯🇵'),
    Country(name: 'China', code: 'CN', dial: '+86', flag: '🇨🇳'),
    Country(name: 'Egypt', code: 'EG', dial: '+20', flag: '🇪🇬'),
    Country(name: 'Turkey', code: 'TR', dial: '+90', flag: '🇹🇷'),
    Country(name: 'Switzerland', code: 'CH', dial: '+41', flag: '🇨🇭'),
    Country(name: 'Sweden', code: 'SE', dial: '+46', flag: '🇸🇪'),
    Country(name: 'Norway', code: 'NO', dial: '+47', flag: '🇳🇴'),
    Country(name: 'Denmark', code: 'DK', dial: '+45', flag: '🇩🇰'),
    Country(name: 'Poland', code: 'PL', dial: '+48', flag: '🇵🇱'),
    Country(name: 'Portugal', code: 'PT', dial: '+351', flag: '🇵🇹'),
    Country(name: 'Greece', code: 'GR', dial: '+30', flag: '🇬🇷'),
    Country(name: 'Israel', code: 'IL', dial: '+972', flag: '🇮🇱'),
    Country(name: 'Qatar', code: 'QA', dial: '+974', flag: '🇶🇦'),
    Country(name: 'Kuwait', code: 'KW', dial: '+965', flag: '🇰🇼'),
    Country(name: 'Oman', code: 'OM', dial: '+968', flag: '🇴🇲'),
    Country(name: 'Bahrain', code: 'BH', dial: '+973', flag: '🇧🇭'),
    Country(name: 'Tanzania', code: 'TZ', dial: '+255', flag: '🇹🇿'),
    Country(name: 'Uganda', code: 'UG', dial: '+256', flag: '🇺🇬'),
    Country(name: 'Rwanda', code: 'RW', dial: '+250', flag: '🇷🇼'),
    Country(name: 'Ethiopia', code: 'ET', dial: '+251', flag: '🇪🇹'),
    Country(name: 'Morocco', code: 'MA', dial: '+212', flag: '🇲🇦'),
    Country(name: 'Algeria', code: 'DZ', dial: '+213', flag: '🇩🇿'),
    Country(name: 'Colombia', code: 'CO', dial: '+57', flag: '🇨🇴'),
    Country(name: 'Argentina', code: 'AR', dial: '+54', flag: '🇦🇷'),
    Country(name: 'Chile', code: 'CL', dial: '+56', flag: '🇨🇱'),
    Country(name: 'Peru', code: 'PE', dial: '+51', flag: '🇵🇪'),
    Country(name: 'Indonesia', code: 'ID', dial: '+62', flag: '🇮🇩'),
    Country(name: 'Thailand', code: 'TH', dial: '+66', flag: '🇹🇭'),
    Country(name: 'Vietnam', code: 'VN', dial: '+84', flag: '🇻🇳'),
    Country(name: 'South Korea', code: 'KR', dial: '+82', flag: '🇰🇷'),
    Country(name: 'Hong Kong', code: 'HK', dial: '+852', flag: '🇭🇰'),
    Country(name: 'Taiwan', code: 'TW', dial: '+886', flag: '🇹🇼'),
    Country(name: 'Russia', code: 'RU', dial: '+7', flag: '🇷🇺'),
    Country(name: 'Ukraine', code: 'UA', dial: '+380', flag: '🇺🇦'),
    Country(name: 'Austria', code: 'AT', dial: '+43', flag: '🇦🇹'),
    Country(name: 'Belgium', code: 'BE', dial: '+32', flag: '🇧🇪'),
    Country(name: 'Czech Republic', code: 'CZ', dial: '+420', flag: '🇨🇿'),
    Country(name: 'Finland', code: 'FI', dial: '+358', flag: '🇫🇮'),
    Country(name: 'Hungary', code: 'HU', dial: '+36', flag: '🇭🇺'),
    Country(name: 'Romania', code: 'RO', dial: '+40', flag: '🇷🇴'),
    Country(name: 'Jamaica', code: 'JM', dial: '+1876', flag: '🇯🇲'),
    Country(name: 'Trinidad & Tobago', code: 'TT', dial: '+1868', flag: '🇹🇹'),
    Country(name: 'Bahamas', code: 'BS', dial: '+1242', flag: '🇧🇸'),
    Country(name: 'Barbados', code: 'BB', dial: '+1246', flag: '🇧🇧'),
    Country(name: 'Dominican Republic', code: 'DO', dial: '+1809', flag: '🇩🇴'),
    Country(name: 'Zimbabwe', code: 'ZW', dial: '+263', flag: '🇿🇼'),
    Country(name: 'Zambia', code: 'ZM', dial: '+260', flag: '🇿🇲'),
    Country(name: 'Botswana', code: 'BW', dial: '+267', flag: '🇧🇼'),
    Country(name: 'Namibia', code: 'NA', dial: '+264', flag: '🇳🇦'),
    Country(name: 'Mauritius', code: 'MU', dial: '+230', flag: '🇲🇺'),
    Country(name: 'Sri Lanka', code: 'LK', dial: '+94', flag: '🇱🇰'),
    Country(name: 'Nepal', code: 'NP', dial: '+977', flag: '🇳🇵'),
    Country(name: 'Jordan', code: 'JO', dial: '+962', flag: '🇯🇴'),
    Country(name: 'Lebanon', code: 'LB', dial: '+961', flag: '🇱🇧'),
    Country(name: 'Cyprus', code: 'CY', dial: '+357', flag: '🇨🇾'),
    Country(name: 'Malta', code: 'MT', dial: '+356', flag: '🇲🇹'),
    Country(name: 'Iceland', code: 'IS', dial: '+354', flag: '🇮🇸'),
    Country(name: 'Luxembourg', code: 'LU', dial: '+352', flag: '🇱🇺'),
    Country(name: 'Ecuador', code: 'EC', dial: '+593', flag: '🇪🇨'),
    Country(name: 'Costa Rica', code: 'CR', dial: '+506', flag: '🇨🇷'),
    Country(name: 'Panama', code: 'PA', dial: '+507', flag: '🇵🇦'),
    Country(name: 'Uruguay', code: 'UY', dial: '+598', flag: '🇺🇾'),
  ];

  static Country findByPhone(String phone) {
    final cleaned = phone.trim();
    for (final c in allCountries) {
      if (cleaned.startsWith(c.dial)) {
        return c;
      }
    }
    return defaultCountry;
  }
}
