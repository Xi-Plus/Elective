SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `admin` (
  `account` varchar(20) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `name` varchar(30) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `class` (
  `classid` varchar(10) COLLATE utf8_bin NOT NULL,
  `name` varchar(20) COLLATE utf8_bin NOT NULL,
  `credit` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `class_time` (
  `classid` varchar(10) COLLATE utf8_bin NOT NULL,
  `day` tinyint(4) NOT NULL,
  `period1` tinyint(4) NOT NULL,
  `period2` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `department` (
  `depid` varchar(10) COLLATE utf8_bin NOT NULL,
  `name` varchar(10) COLLATE utf8_bin NOT NULL,
  `director` varchar(10) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `elective` (
  `stuid` varchar(10) COLLATE utf8_bin NOT NULL,
  `classid` varchar(10) COLLATE utf8_bin NOT NULL,
  `score` int(11) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `login_session` (
  `type` tinyint(1) NOT NULL,
  `account` varchar(20) NOT NULL,
  `cookie` varchar(32) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `student` (
  `stuid` varchar(20) COLLATE utf8_bin NOT NULL,
  `name` varchar(10) COLLATE utf8_bin NOT NULL,
  `depid` varchar(10) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `admin`
  ADD PRIMARY KEY (`account`);

ALTER TABLE `class`
  ADD PRIMARY KEY (`classid`);

ALTER TABLE `class_time`
  ADD KEY `classid` (`classid`);

ALTER TABLE `department`
  ADD PRIMARY KEY (`depid`);

ALTER TABLE `elective`
  ADD PRIMARY KEY (`stuid`,`classid`),
  ADD KEY `classid` (`classid`);

ALTER TABLE `login_session`
  ADD PRIMARY KEY (`cookie`);

ALTER TABLE `student`
  ADD PRIMARY KEY (`stuid`),
  ADD KEY `depid` (`depid`);


ALTER TABLE `class_time`
  ADD CONSTRAINT `class_time_ibfk_1` FOREIGN KEY (`classid`) REFERENCES `class` (`classid`);

ALTER TABLE `elective`
  ADD CONSTRAINT `elective_ibfk_2` FOREIGN KEY (`classid`) REFERENCES `class` (`classid`),
  ADD CONSTRAINT `elective_ibfk_1` FOREIGN KEY (`stuid`) REFERENCES `student` (`stuid`);

ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`depid`) REFERENCES `department` (`depid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
