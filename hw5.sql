CREATE DATABASE IF NOT EXISTS grades;

USE grades;

CREATE TABLE studentInfo (
  id smallint(6) NOT NULL PRIMARY KEY,
  firstName varchar(15) NOT NULL,
  lastName varchar(15) NOT NULL
);

CREATE TABLE quizzes (
  id smallint(6) NOT NULL,
  quiz1 smallint(3) NOT NULL,
  quiz2 smallint(3) NOT NULL,
  quiz3 smallint(3) NOT NULL,
  quiz4 smallint(3) NOT NULL,
  quiz5 smallint(3) NOT NULL,
  midterm smallint(3) NOT NULL,
  final smallint(3) NOT NULL,
  quizAvg smallint(3) GENERATED ALWAYS AS (
    (quiz1 + quiz2 + quiz3 + quiz4 + quiz5 - LEAST(quiz1, quiz2, quiz3, quiz4, quiz5)) / 4
  ) STORED,
  PRIMARY KEY (id),
  FOREIGN KEY (id) REFERENCES studentInfo(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE homeworks (
  id smallint(6) NOT NULL,
  hw1 smallint(3) NOT NULL,
  hw2 smallint(3) NOT NULL,
  hw3 smallint(3) NOT NULL,
  hw4 smallint(3) NOT NULL,
  hw5 smallint(3) NOT NULL,
  hwAvg smallint(3) GENERATED ALWAYS AS (
    (hw1 + hw2 + hw3 + hw4 + hw5) / 5
  ) STORED,
  PRIMARY KEY (id),
  FOREIGN KEY (id) REFERENCES studentInfo(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE records (
  id smallint(6) NOT NULL,
  hwAvg smallint(3) NOT NULL,
  quizAvg smallint(3) NOT NULL,
  midterm smallint(3) NOT NULL,
  final smallint(3) NOT NULL,
  grade smallint(3) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (id) REFERENCES studentInfo(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO studentInfo (id, firstName, lastName) VALUES
(1, 'John', 'Smith'),
(2, 'Jane', 'Doe'),
(3, 'Carlos', 'Williams'),
(4, 'Baz', 'Debany'),
(5, 'Axel', 'Davis');