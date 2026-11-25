"use client";

import Image from "next/image";
import Link from "next/link";
import { useState, type CSSProperties } from "react";
import styles from "./home-motion.module.css";

const cards = Array.from({ length: 18 }, (_, index) => index);

export function HomeHero() {
  const [paused, setPaused] = useState(false);

  return (
    <section className={styles.hero} aria-labelledby="home-title" data-paused={paused}>
      <div className={styles.heroStage}>
        <h1 id="home-title" className={styles.heroTitle}>
          <span>BDJG</span><span>STUDIO</span>
        </h1>
        <p className={styles.location}>Based in Indonesia <span aria-hidden="true">◎</span></p>
        <div className={styles.orbit} aria-hidden="true">
          {cards.map((index) => (
            <div className={styles.orbitSlot} key={index} style={{ "--angle": `${index * 20}deg` } as CSSProperties}>
              <div className={styles.orbitCard}>
                <Image src="/images/home/placeholder.webp" alt="" fill sizes="(width < 810px) 24vw, 170px" loading="eager" />
              </div>
            </div>
          ))}
        </div>
        <p className={styles.disciplines}>Photography<br />Film Production<br />Post Production</p>
        <Link href="#book" className={styles.contact}>
          <span className={styles.bubble}>Hi!<br />Punya<br />cerita?</span>
          <Image src="/images/home/placeholder.webp" alt="" width={80} height={80} />
          <span>Mulai cerita<br />bersama BDJG <span aria-hidden="true">↗</span></span>
        </Link>
      </div>
      <div className={styles.heroBottom}>
        <a href="#works" className={styles.scroll}>Scroll Down <span aria-hidden="true">⌄</span></a>
        <button type="button" className={styles.motionControl} aria-pressed={paused} onClick={() => setPaused(!paused)}>
          {paused ? "Lanjutkan animasi" : "Jeda animasi"}
        </button>
      </div>
    </section>
  );
}
