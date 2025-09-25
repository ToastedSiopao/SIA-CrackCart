 <?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User info from session - updated to match your new session variables
$user_id = $_SESSION['user_id'];
$user_first_name = $_SESSION['user_first_name'];
$user_last_name = $_SESSION['user_last_name'];
$user_role = $_SESSION['user_role'];
$user_email = $_SESSION['user_email'];

// Connect DB
include("db_connect.php");

// Producer data with prices (you can later move this to a database)
$producers = [
    [
        'name' => 'San Miguel Egg Farm',
        'location' => 'Bulacan, Philippines',
        'logo' => 'https://scontent.fmnl3-2.fna.fbcdn.net/v/t39.30808-6/309197041_397533832570608_2852504124934330080_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeHQAPfsIN59elLsZq6GgMkGtqGVNb0xigq2oZU1vTGKCjQEN2IV6VJS3bcuZzVX_1vGNoFrIf-yEPiyv_e-s-WU&_nc_ohc=Kjracd8B_ZsQ7kNvwEIM2bV&_nc_oc=Adlo_7JOBJAxRwQ-675ShEefHtRiSs0g6L2VYML5UKnDjJ1aBvmJ4HNnXv2bRf-zsr0&_nc_zt=23&_nc_ht=scontent.fmnl3-2.fna&_nc_gid=dntFvBv9oa901C-sCTD3yA&oh=00_AfYBGO7xemNLZmUoZFlxkurMV50-2iS9OPq8hKWMq-0lzw&oe=68C06DE4',
        'url' => 'https://www.facebook.com/sanmiguelgamefarm',
        'prices' => [
            ['type' => 'Regular Eggs', 'price' => '₱7.50', 'per' => 'per piece'],
            ['type' => 'Free-range Eggs', 'price' => '₱12.00', 'per' => 'per piece'],
            ['type' => 'Jumbo Eggs', 'price' => '₱9.00', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'Kota Paradiso Agricultural Farm',
        'location' => 'Laguna, Philippines',
        'logo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAABSlBMVEX////RVzCCSDr8/////f4AYCoAXSQAVh3U49qvyb0AZDHRVCuMW1Hx7uxpmoF9Pi6ARTb69/rNwL31/fp7OSjQSxv+//vQUSb18Opklnk4e1gAYjbd0c3RSx7h3NmtlYmrkIrl8euGVElyNCB5Qi7r5uNwMRazmpOGRTzgtqXNUyTIRwDbrp3s29HORAzXfGPOWTLGSBPXoozu6OnTyMEAWyighXq5pZ7E2c2LZFd/Tjx9NByefXJyNiTl19h0KRHVeF/YjXJ0LA6aamLr0MbOe1iacmSNYlDoxrbaj3vGXz3UlnxrLADNb0x2LySgc252PCJ9VEHpz8rIsqnv6t7TbUfjs53cjHzRhGXfxbLnvLHgt5vDVSfPVjnFMgDXpIvYkHCStaEATwBMh2d5oY6307+oiXmWwa+OrZ/exUDTAAASEklEQVR4nO2c+1/aytaHE4ZAAC2RJiCTpC3BCyAlQCBqxQu2uosW4aX1FG3dvu59Xlv33uf///VdkwuEi1WqYtszz6e1MBnCfLNm1lpzsQxDoVAoFAqFQqFQKBQKhUKhUCgUCoVCoVAoFAqFQqFQKBQKhUKhUCgUyq9K6l9zrx+7DffL679rDHLf+JlcRhK3HrM9909WXKn138W3JZ+48HiteQBCOz5xo//2UPRJy3F0ff2fjxwozPfebSR90nHsEZvzACREn5hw3+STPi7zi/kZZkH0vY/J9uv8js+3kvh2/Z+PZUnadl4ugAVXuo/amvsH5TK9YQgCfZnat+v/hNREXyZlvaqTMZi/ofpPyDKEP/JvfEv0+XbyjPzYDbpv0jskvstMDgK9b+XX66IMsyj6ktBJE7tE4K/mZCyOX0oiE6pnfD5pN/XYjbl30rXXKQj3+XURhqC4nXvs9tw73TcihAcAfr7M1OOTfNb/UI26T3JvQNxLS6JPyiQmaHVsYePmSg8PQjzDf2uKkBd9DtJKfZJcez0jvvkRcnO5UNnbP/9GdFt0FErvt9KT3Hj9N5/09q6tuzMy02CxIihYL6Dr7Jj6LSkRgW8n0sfEMj4pq969iXelYCgsKyi6gA+unc3GUocgUZpwxeIdZD6TPZMHADFNg2UV/eyoqglaK3btaOSIESdpr8x0M9zO4ycG/hNTYPFZDDxNU1eUlsqPr5daIeMwO0myHT/mxB8hOd9TWNy2X5axIBxcU62e9XESB7PD0G1uavWEuuRbvpcm3o0mZpUzx258wxRwYWw1NbMtzYFHzdyym/pziXcix0kL3Ud2NH5V14WLfs9cVXRdHhc1YGYodlNJn3iLGcXa4paYySQlziclV948dgLbVliz0X8rX1xjREny7SIm6ZPmrrnR+sLfa/ar1JssZ5NdTuSYa8b1tMgZrLLvLTjH+ukYd7oO1ltkmA3RtzO213WPk6J4nLOyORix2ewux/nE+kAdxKQSqWsj7kNxIOh6zPOUZVkXzDEa3oK0nC10zOraOuTjxGaZQ/LR7v9kthdTbzlpedB+6be7yeT2dNeP5bTB4vPBsiMd/z5cD5GFYEi+eH+Gk4YS6RAThwnjdvYYms+JHBnDOaJza2gKKW+sSPAQklMelhVB6AwVNTR9dCCSyf06ebEhcZnQ4MjKbYuctLuYyqW7We7YtXD3eHDEvpaynCRNXeFakS2Wh3qNaiirw/Vi732SZL1KuFL717ISd3xod+yueOy6Wt9gd15fkcTMXF3Krkw0s7wzbUU/Gl4vk02hPVwvT0KFfRW0fvBc4UPbYFVXVjqZdGSldrik58aJFW53ESZRsY2prpETr2KcjBQrytFQCZ98ySWdZw95ynvvfC8vcple6rmQzMq94t6kyU8yvl1i+akH/wLWRszFMJoyXNgVeyvdCIK+NzeNrXAkitikkklX7KHkSQ3UrHS8TjzuyvKUNZ6yWB0ZFjGsDXmaEMx+d3tN47iXO30j1kTuOO6saCRWjnuTq+WsZxhuiFKdUeuZbFbaVpkpRouyAdYaWW9pYKM8WEJM2N/n7R5zL/sB41CSnLCePjxObvVaP5ftW1rNcNzyRjK7Ul9Mitnu/P0puIlVDY/pNCXdGCj1h7IvpfeeIsjf+rPEw6xPTMVyqdpcRtz1TAXrWS5Zy+UsxTWSxEnJd5Ax5P4lipnD9enY0S+bytGYnLEq6IMFNfFl0rtVn4DkdDvktLELs8bjTGYnmXy/6HVAiRUfJ2Z+s4boBiiUdsiwhOxgK8llj99OJWTwTW24OwLoIx7SrUJClhxo0TvJs5ff5VYyxzuZueFJUi0L5XXL1hsZ0bM+1xUz4m9TmfjzB0pndO8IdQTcGFAI6czQjGkN7JZ0imA6HHudSo+ziewWri10veZFqVp+KuuLKsZN+IdHHj0h1NSUlrcWyUh92aFsuZuEofj46y830dRMsCBSK54cLaTqYMIB//pW8iVH8pA62T380SWiinZELHMAo9GxkJ/h9xSlMmCv2BvfyzEriHMi50sujpb/SEAnbSASE1njpKfpyBRaQx7jQ2ZldJsJhZbJBtTcj7wBhZo6ZpCf2dfd6ZOM0JEmFEfca2qsVwjNiZJPWsnL00xRJuOMpKT+k6Kguas08j5mjcZohLxml6lONtuSYm26s6HbI2uYKFtVBNt1IubkVBGM5gS3qK2QRXBxco8zne3EBiaeVDVYzV7EQAWsKOYYC16LzOS2rN2a3+KTrqYdmec3V7obiGljssJW0lhMhhkqdzRBZ0fnijeQersjJo9v/DaUqxQ8T6FhjJuW3jc6bsh+WWBZof2xUTrFMBc+Qt9xOibWXVi7qY6/bApFj79qYlZrPvQqag5jlWfOMdlU07AmKEZnNEe9L/iWw3pXKFVjjMu+bz7hPfjZEhS901GwZlZ+5x/ufFMDs0LLG1Q+tlYf+jQVOiDz+IYmHJAvOsk96Negis7j0kN+whhk04DRsyeYZLmbJ7PcB/wycNiCrk45MfhoVBl0Yij7U9g1Qecaq03ZhMyqtsrwnxVcnsa+0Omgn5kGfqZjNJBaV4QX9b/Bd41UhHhk7W71Vg3mj/b6W3l+Buamavm+VGp88yzP5PCqgYkdlkhytsd+4udKQ8rlRY44U1nD7CvoTBO8SSfl1VPTwJpmtO53Rwo1jDNG1pXqtXdFzt8+Hw3FIEUIxRvnhbLHpDxCzmiuIjNys2JibGKFZXurBhA3WMPZ2eKZZstQBIEVICQXZX/vc79/FvbuGi5XjXOmoOHCNQrlwlm1uC8PXK3AYCIKT86KmoJNz7XyUUtvfRy5R0nRFEgnWEDp+ZkO6Kk6T0M+w4KAsf7Haaey2p9oNi/I50b2iyakY6ioyurymLsgv/zJ1CCbw97NC6Sagl4BNXsGabbh7hrzzOsDsAOrmDGPFeG+zQtovlLcO4J0hnWTUESGpFm2rCUzR5hVTj96owh8rtzBrAXeu1N80TuQG4IHH+c+ytYzhO8wvVcLGvS1UoecDTMrpbTbheW2IViVcdPbHnnfEKqK2VYRL5tgNfd5HMGQbNrb3HLZYKEZyD/wubahw+PCkEaSEPr9AZPxv0xnaGXbucIUoMmKXjFZtuj5bnRAZMCg0aqF/rkNdNLSWN3ssAqL+04rhNQWPCNj37q/CpZqO5+ImYLWW/aCZ6YMREnyOXiOSrHdbJwfwNUxm0a3pqWWDW1kG5RQgvyjeBRCq4ax6lGomlbXUZSCd2e+jHVWazX4smko/cNi/MkfpLiMrN64D+obzrVPiqD3Ni1ggsGeersJf0K8jlkKMcTKbUWo3EEheG2heDLSz2WmhKtatWydVhyIDQXLYxhn3rEBwhQBl3jGL6sF78wBGlpctfsx+gQ6irbVZUj0+7kNki8UVul4dqJk6KC45c4by8bILuZEnGDl85jFhAJM+dtkZKgMGsh2OkRgsTRwtELVFEUgXn3AYSH5D4UtNp348cmA6VnH+aayxhqeWWJZ14jLess8FVC8J7vj8lzXRs5LTADfHjtBg8GvHZENjapx4fXWcq5ILDiQH4T4ji4I8MTVo6Lh9Vlt0GGtUSD//GcD5k2CMxz4VaV3usxCLQkw6TA/Wp9FMFcVqm7c5+WWot9lwgO+vzPSR6HfCMIej9SKBo7e9CyFohJ4gOH5D/EUZYYvmEqVLfa3VNUiuAj73uWq0Tohw9C5dCrghrfViORV4DPt2hcCq/WeOqoo10br24BKujaaghEdWilWgoBuQDC7KPO9TKUK/vVssDb4HqE1X+7AUDTBaiV3PDVxtWrlJ8RZHcHYY12fDXHDHIzAUA282IX1utw/mxVi1I6Gh75vMmBMX4zGQpl4E81U9D8K5UZFU4y2a8YyuAtzaF0Ynj6rY003jz6WS1jAp05W80mzYwAYkG3Al1R7cTWNlRHv2FbcvgFdwln1Y9RVQ8HjdjZvi585V7R/j5Y3DDuZqFgtKhi6gltNtx1aabDP8IpghQ/B6gsnLGisloiFCtoF6MjtF4225UO03n5rwnM+wF4pz3WwYJ7ZNy7AMDw9CcFM47OhY33yLH+AljAu2pNlKVZxwyBq6FhgjVWyV6yDzxvMofwqeRwCbqmkJyO/Cm0FVz9vnwSstgzjj7KVpzOa4q7QvDaKvW+Nrx7snUHejU13dOeI18WAomhmaVw+eXvkBhYqY+6gsgY2DvouVl1VDPwnJF4wRMzPw7X3i5pRLfSiGTqH9hqkuYUiJld6Q1hru/6x1U9SCn9iDWOjVeo/6YIB0UfDWrHzrV+KuB0HQz7NQUblsuqZByEkl8+bKMTHK/rnUZunG2VSpVebyRXOY6T/qY3GSX/l/lNvLwt5VvPkZqm92jzxTKzhc+f/bq8WGjHEf8+6rRcYb9Wba/3M/J8wfCLoFwOiffGX+93WAVY1fKeU9ocHtf/UT36K3wf8XhA6kad+mJxCofwXggb20vyuZ0XDR2O81dzfhULj9uFCw1d45vKZzdNnz5649519OturMfvUvn65ZLWB2fz6xHvDpWdXdzmy4W68p+1zsGo6TdZ/UHwN9UtVMh8kxf4Tpy4Qtz8rk4njWjrtzB/9dua55skj0OyXF1GXL0tWW/18JBzp1QiG7YvhmStL4Ux0ZsnTxK/hGa/iSXnn/O8k3cQhaXCtm6iRs5QpckAv9iGRqMvOG3K+OW4fnk0kttYTMDtalJ3yRC3RtS+t24fyFz0K/bORYCDs8PretsaTcPCV02w/EwgGw5FIOBwIRq4YPsTMBKNf+wtdmzPB8PcrRKl16/hrDWylWgpVu822KHiTizlvNvoKGeZva8Jg6SDlCfj4grVytJhIDytEs5FAcPOJjWub56DqeV9h+Mnm5uaTr9HgzCxDFAZf/eU+Hz4QuItCZoHpkvXbOjnjSlpVA0EfGI/ZCOkhG9qyQIf7ksg6tGbqCbtwWOHzoa8FswaDkdmewle28mfR6JWtMBB0/cB/wsG7KFS3ErW83fxc7W+i8O/8Vuy2CvNxV2F9Yc62XTfxjtjyJoVPw8FoMPp0SCG/GY4+sxUGw5d2zSXrzfcrrKVVuQ4jKkHOo5GnDza0Tv1YCvPQ6vRrhpkHq8pjFK4n7L/Ehl0ynufrcjxFbrXg8X2OQr+/X8bD0PonHIzMexWGQsxlFFTbCgOWs+GZr4G7KTwkYkiTahsLeaIwH2PkLVchs5HPW9ZYr9c+ECcZX67VFtZQz7qLCwtWp0yk7F5aew3BgNxzIZ/Pu4eJx9jwMgwlz4ORf1yFllb+yatg5Iml8MXzoP2Zv8KB6PPA9yv0POlQaFxx3C2N96oNxMD4dcct/aFQb9mGDDorGEQunVuDb/2LAT1BEh1tX/rqVWQmDMOPHHaZCb6anSFiGTkQjF5dRe/iaaaApdAi8MVWuBkJRnhmHiy2yTgKbcJB0ltB4QxzBcbj4Wcwyjx9RIW3yjSIwsBMJDIzM2O7FubZiygJ7VdRCHuOwsgMhJTwJQmClsL5+WggenVkWfIxFd4KaxzOLxHsgqWZQHgWCkA5cSfWOFyaX4rYJnUUMk8iwfDzABmNP4dCb8FlFPojWBTGYfg/pB/YvvQyDDkA31NIkoKA9Qh+PoXRAIno9siMkgJb4XzEzmRchSQShq+Y+bspTHWBWo9uN5FYT6VSJLPO5XIxC1WN9YjHyd+4+w6uEWSLeEh2IHe2higapxC6X/SFReCF5TDdiH8Zteq5Ch1nc0cb8si7ThOCpq+t5UAeqFwHEomu/o2MwMLGRt3h0ObtFmFubnl5mSO/ny0BWfIbeHMfFu2kflghhDcnX3E1OQrnZ6yR6CoM8c9nrIF55166ZikiRiMGAxOFQvezhuo8uyGFEOl6cyPoiJBqW56GvL0K2xUdG7rcWWEOzJVwOuviomWfrfrW4dbyNseBYXzbHLEJ2Gh5bs4xGGGL/HHekks2pBqX3c1yh4tdv6twJupR+CQCuafLszDppsHoF0vR/EzgC3ksXwJfBhRalaZEKO4dlYOosv0zPjLzv3qx2Xvtn//6vD+9XXr+FbRsfv3HfvtP8NLzj8ssVHrgVd3BBQ175PqH6F+5xS1uKmZu9z/7UCgUCoVCoVAoFAqFQqFQKBQKhUKhUCgUCoVCoVAoFAqFQqFQKBQKhUKhUCgUCoVCoVAoFAqF8tPy/2AMOLRF9yGKAAAAAElFTkSuQmCC',
        'url' => 'https://kotaparadisofarm.ph/products/native-egg',
        'prices' => [
            ['type' => 'Native Eggs', 'price' => '₱15.00', 'per' => 'per piece'],
            ['type' => 'Organic Eggs', 'price' => '₱18.00', 'per' => 'per piece'],
            ['type' => 'Free-range Eggs', 'price' => '₱13.50', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'Golden Yolks Farm',
        'location' => 'Laguna, Philippines',
        'logo' => 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/448766456_122153540054220120_2192821754871017603_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeFUE-sgR31Io_6M_3adbg9NoFDTI02nd9KgUNMjTad30n-Ao6YpmES_777YXdZBfW-1IiuxmxlWZQHV59sZDBH3&_nc_ohc=UCTn6LKrXk4Q7kNvwEx7LBW&_nc_oc=AdmjBnabrPFLep1F75ar9tp2lImUBhQzLX-QjUNUBO8wQf1I5UzZTKolPW7Lveyv05k&_nc_zt=23&_nc_ht=scontent.fmnl37-1.fna&_nc_gid=5fQmpGWy1BV5EHLocaqL3g&oh=00_AfaaKkJ5hiUa54tAaM1fdoPFtsgmbtG8VGI0l_bXowcx4A&oe=68C05BFB',
        'url' => 'https://www.facebook.com',
        'prices' => [
            ['type' => 'Premium Eggs', 'price' => '₱10.50', 'per' => 'per piece'],
            ['type' => 'Jumbo Eggs', 'price' => '₱11.00', 'per' => 'per piece'],
            ['type' => 'Brown Eggs', 'price' => '₱9.50', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'FreshNest Poultry',
        'location' => 'Pampanga, Philippines',
        'logo' => 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/536278500_771779282457560_1891929734049571049_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeETIUvH4kiQon_HMo8ezQOZcYaJwMH6zDRxhonAwfrMNMApVeA6jwGlFJiP2DSGpqJ5spbellzzKUVeM4q6UyHP&_nc_ohc=metKu-5eTucQ7kNvwHA2MP6&_nc_oc=Adm_rRR_l_X5hIv2UlenFVwtk4Loo4pthDRNFb5ITzlEt-jBFGbq5Q1UIHeMFfTVVbc&_nc_zt=23&_nc_ht=scontent.fmnl37-1.fna&_nc_gid=lndlIPqg5txMZH_fj6hgEA&oh=00_AfbQvmYMug2l2Ilv8cnQcpBFTcL1OJSt-RHi9bKaGpx9Xw&oe=68C050CC',
        'url' => 'https://www.facebook.com/FreshNestFarmPH',
        'prices' => [
            ['type' => 'Fresh Eggs', 'price' => '₱8.00', 'per' => 'per piece'],
            ['type' => 'Medium Eggs', 'price' => '₱7.00', 'per' => 'per piece'],
            ['type' => 'Large Eggs', 'price' => '₱9.00', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'Happy Hen Farms',
        'location' => 'Quezon City, Philippines',
        'logo' => 'https://scontent.fmnl3-4.fna.fbcdn.net/v/t39.30808-6/326506021_494812559393671_6721513954783849887_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeHVy1uicA-x8_FdsXa0QP8m2qPmNtH8zuvao-Y20fzO67a0yg7S4Yqj_bbLy-pV1yKtTZcS63cgw5ZKhQogG_Km&_nc_ohc=G7K8KZQzzooQ7kNvwEpm6nz&_nc_oc=AdmcLo1-pK6LDaMpzCxwwhkF6_BNV5zKKJyek7VTIDsftOZpzi3EKIlAbUG7yozmWGA&_nc_zt=23&_nc_ht=scontent.fmnl3-4.fna&_nc_gid=6E5RpVB8jpcw-ZYN5kzuiA&oh=00_AfZFQlWE6UN-OZuK1wk7YP1YR-WJ036Ew-jUMiVBhPTQJQ&oe=68C06FCF',
        'url' => 'https://www.facebook.com/happyhenphilippines',
        'prices' => [
            ['type' => 'Happy Eggs', 'price' => '₱8.50', 'per' => 'per piece'],
            ['type' => 'Farm Fresh', 'price' => '₱7.80', 'per' => 'per piece'],
            ['type' => 'Special Eggs', 'price' => '₱12.00', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'Eggcellent Layers',
        'location' => 'Cavite, Philippines',
        'logo' => 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/275662495_153406203790612_5611612134323118829_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeHGwbtIyHHf4ejSJQ4z-cPKil8ac-zfCruKXxpz7N8Ku1qyjHc83BrHnoXnfatsLnvtAfrjFf8U0hyEmZ-OPm6P&_nc_ohc=zOFSLIQ9EksQ7kNvwGQB-8E&_nc_oc=Admg7dmOM6n3xEiSdb1P5HLuTb__s5T8CAYMpiEZtEz6LkNPqMZrv6HR5DXCBGMnmdM&_nc_zt=23&_nc_ht=scontent.fmnl37-1.fna&_nc_gid=qSuoRApgMpX5OKDsVrK7bg&oh=00_AfYrnKwua2MOeq-I1Ll5tVmozpzqvk4Be-8nX4INcKskwA&oe=68C060B0',
        'url' => 'https://www.facebook.com/EGGCELLENTBUSINESS',
        'prices' => [
            ['type' => 'Grade A Eggs', 'price' => '₱8.20', 'per' => 'per piece'],
            ['type' => 'Extra Large', 'price' => '₱10.00', 'per' => 'per piece'],
            ['type' => 'Standard Eggs', 'price' => '₱7.00', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'SunnySide Egg Farm',
        'location' => 'Pasig, Philippines',
        'logo' => 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/244206017_101518792307481_6975821136247613608_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeH5xUxNyRrdZ_dgSM09sYD67kdHobrUVsjuR0ehutRWyKlgiCVQPfD_FhuBpJfrJqWQk8YERq6eRgBMO0m9wVF3&_nc_ohc=AvTpaM9q8zQQ7kNvwEaJSKp&_nc_oc=AdnQIchtgigr0dqTQxwF2zhFjwFRnsuDV0ZL-2Zw8J-HCS70moIgDlY3vXpr3591Bz0&_nc_zt=23&_nc_ht=scontent.fmnl37-1.fna&_nc_gid=IrhsQjfIcmLqoODhuBGmsQ&oh=00_AfbfoP16xC2JtqyrmDQGdZyROB4k16rw8MI1qBBxd6WFmQ&oe=68C0542E',
        'url' => 'https://www.facebook.com/profile.php?id=100082500728747',
        'prices' => [
            ['type' => 'Sunny Eggs', 'price' => '₱8.80', 'per' => 'per piece'],
            ['type' => 'Fresh Daily', 'price' => '₱7.50', 'per' => 'per piece'],
            ['type' => 'Premium Quality', 'price' => '₱11.50', 'per' => 'per piece']
        ]
    ],
    [
        'name' => 'FST Egg Store',
        'location' => 'Iloilo, Philippines',
        'logo' => 'https://scontent.fmnl3-4.fna.fbcdn.net/v/t39.30808-6/333611225_578136657594857_8081151375127004928_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeFP8h4muPl-ewmTTsjAra3EAfOtIs2cvqYB860izZy-pmApDlpeB8PYmYrHdImXhhth0IgteJu_odsxgLj9iCdy&_nc_ohc=INYDHRE8DU4Q7kNvwHdlNi0&_nc_oc=AdkjJiKgk95xkjl_uR5n_I9glvUiXGEULc1_Wz3ETopVS993HeCDZy1iK7HcaBchdCg&_nc_zt=23&_nc_ht=scontent.fmnl3-4.fna&_nc_gid=pYT4RbUXrQOkf_63ynvOpg&oh=00_AfYdb3f0FQMfbcaIddxWrw0XTmgikCF-0r1ZJMxZ9QfxWg&oe=68C07A8C',
        'url' => 'https://www.facebook.com/fst.eggstore',
        'prices' => [
            ['type' => 'Local Eggs', 'price' => '₱7.20', 'per' => 'per piece'],
            ['type' => 'Imported Eggs', 'price' => '₱14.00', 'per' => 'per piece'],
            ['type' => 'Mixed Pack', 'price' => '₱350.00', 'per' => 'per tray (30 pcs)']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CrackCart Producers</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #fdfdfd;
      font-family: Arial, sans-serif;
    }
    .sidebar {
      background-color: #fff;
      min-height: 100vh;
      border-right: 1px solid #eee;
    }
    .sidebar .nav-link {
      color: #333;
      font-weight: 500;
      margin-bottom: .3rem;
    }
    .sidebar .nav-link.active {
      background-color: #ffb703;
      color: #fff;
      border-radius: 8px;
    }
    .upgrade-box {
      background: linear-gradient(45deg, #ffb703, #ff9e00);
      border-radius: 12px;
      padding: 15px;
      color: #fff;
      text-align: center;
      margin-top: 20px;
    }
    .producer-card {
      border: 1px solid #ddd;
      border-radius: 12px;
      background: #fff;
      padding: 20px;
      transition: 0.3s ease-in-out;
      height: 100%;
      position: relative;
    }
    .producer-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transform: translateY(-5px);
    }
    .producer-logo {
      width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
    }
    .btn-producer {
      background-color: #ffb703;
      color: #fff;
      border-radius: 8px;
      padding: 6px 14px;
      border: none;
    }
    .btn-producer:hover {
      background-color: #ffa502;
      color: #fff;
    }
    .price-tag {
      background: #28a745;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.85rem;
      margin-right: 5px;
      margin-bottom: 5px;
      display: inline-block;
    }
    .price-list {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #eee;
    }
    .navbar-yellow {
      background-color: #ffeb3b;
    }
    .search-box {
      margin-bottom: 20px;
    }
    .filter-buttons {
      margin-bottom: 20px;
    }
    .filter-btn {
      margin-right: 10px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-yellow shadow-sm px-3">
    <div class="container-fluid">
      <!-- Sidebar toggle (mobile only) -->
      <button class="btn btn-outline-dark d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i>
      </button>

      <!-- Brand -->
      <a class="navbar-brand fw-bold" href="dashboard.php">CrackCart.</a>

      <!-- Right side -->
      <div class="ms-auto d-flex align-items-center gap-4">
        <!-- Notification Bell -->
        <a href="#" class="text-dark fs-5">
          <i class="bi bi-bell"></i>
        </a>

        <!-- Username + Profile -->
        <div class="dropdown">
          <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="me-2"><?php echo htmlspecialchars($user_first_name . ' ' . $user_last_name); ?></span>
            <i class="bi bi-person-circle fs-4"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profilePage.php">Profile</a></li>
            <li><a class="dropdown-item" href="profilePage.php">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row flex-nowrap">
      <!-- Sidebar -->
      <div class="col-auto col-md-3 col-lg-2 px-3 sidebar d-none d-md-block">
        <ul class="nav flex-column mb-auto mt-4">
          <li><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a href="order.php" class="nav-link"><i class="bi bi-cart3 me-2"></i> Order</a></li>
          <li><a href="eggspress.php" class="nav-link"><i class="bi bi-truck me-2"></i> Eggspress</a></li>
          <li><a href="messages.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
          <li><a href="history.php" class="nav-link"><i class="bi bi-clock-history me-2"></i> Order History</a></li>
          <li><a href="bills.php" class="nav-link"><i class="bi bi-receipt me-2"></i> Bills</a></li>
          <li><a href="profilePage.php" class="nav-link"><i class="bi bi-gear me-2"></i> Setting</a></li>
          <li><a href="producers.php" class="nav-link active"><i class="bi bi-egg me-2"></i> Producers</a></li>
        </ul>
        <div class="upgrade-box">
          <p>Upgrade your Account to Get Free Voucher</p>
          <button class="btn btn-light btn-sm">Upgrade</button>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col p-4">
        <h3 class="mb-4 text-warning fw-bold">Producers</h3>
        
        <!-- Search and Filter -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="search-box">
              <input type="text" class="form-control" id="searchInput" placeholder="Search producers...">
            </div>
          </div>
          <div class="col-md-6">
            <div class="filter-buttons">
              <button class="btn btn-outline-primary filter-btn" data-filter="all">All</button>
              <button class="btn btn-outline-secondary filter-btn" data-filter="cheap">Budget (Under ₱8)</button>
              <button class="btn btn-outline-secondary filter-btn" data-filter="premium">Premium (₱10+)</button>
              <button class="btn btn-outline-secondary filter-btn" data-filter="organic">Organic</button>
            </div>
          </div>
        </div>

        <div class="row g-4" id="producersContainer">
          <?php foreach ($producers as $producer): ?>
          <div class="col-12 col-md-4 col-lg-3 producer-item">
            <div class="producer-card">
              <img src="<?php echo htmlspecialchars($producer['logo']); ?>" class="producer-logo" alt="<?php echo htmlspecialchars($producer['name']); ?>">
              <h5 class="fw-bold"><?php echo htmlspecialchars($producer['name']); ?></h5>
              <p class="text-muted"><?php echo htmlspecialchars($producer['location']); ?></p>
              
              <!-- Price List -->
              <div class="price-list">
                <h6 class="fw-bold mb-2">Prices:</h6>
                <?php foreach ($producer['prices'] as $price): ?>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small"><?php echo htmlspecialchars($price['type']); ?></span>
                  <span class="price-tag"><?php echo htmlspecialchars($price['price']); ?></span>
                </div>
                <div class="text-end mb-2">
                  <small class="text-muted"><?php echo htmlspecialchars($price['per']); ?></small>
                </div>
                <?php endforeach; ?>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="<?php echo htmlspecialchars($producer['url']); ?>" target="_blank" class="btn btn-producer">
                  <i class="bi bi-link me-1"></i>Visit Page
                </a>
                <button class="btn btn-success btn-sm order-btn" data-producer="<?php echo htmlspecialchars($producer['name']); ?>">
                  <i class="bi bi-cart me-1"></i>Order
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const producerItems = document.querySelectorAll('.producer-item');
      
      producerItems.forEach(item => {
        const producerName = item.querySelector('h5').textContent.toLowerCase();
        if (producerName.includes(searchTerm)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });

    // Filter functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
      button.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        const producerItems = document.querySelectorAll('.producer-item');
        
        producerItems.forEach(item => {
          const prices = item.querySelectorAll('.price-tag');
          let showItem = false;
          
          switch(filter) {
            case 'all':
              showItem = true;
              break;
            case 'cheap':
              prices.forEach(price => {
                const priceValue = parseFloat(price.textContent.replace('₱', ''));
                if (priceValue < 8) showItem = true;
              });
              break;
            case 'premium':
              prices.forEach(price => {
                const priceValue = parseFloat(price.textContent.replace('₱', ''));
                if (priceValue >= 10) showItem = true;
              });
              break;
            case 'organic':
              const producerText = item.textContent.toLowerCase();
              if (producerText.includes('organic') || producerText.includes('native')) showItem = true;
              break;
          }
          
          item.style.display = showItem ? 'block' : 'none';
        });
        
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Order button functionality
    document.querySelectorAll('.order-btn').forEach(button => {
      button.addEventListener('click', function() {
        const producerName = this.getAttribute('data-producer');
        alert('Ordering from: ' + producerName + '\nRedirecting to order page...');
        // You can redirect to order page with producer parameter
        window.location.href = 'order.php?producer=' + encodeURIComponent(producerName);
      });
    });
  </script>
</body>
</html